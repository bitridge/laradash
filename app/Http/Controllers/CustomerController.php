<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Traits\ManagesProviderAssignments;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    use ManagesProviderAssignments;

    public function __construct()
    {
        $this->middleware('permission:view customers')->only(['index', 'show']);
        $this->middleware('permission:create customers')->only(['create', 'store']);
        $this->middleware('permission:edit customers')->only(['edit', 'update']);
        $this->middleware('permission:delete customers')->only('destroy');
    }

    /**
     * Display a listing of the customers.
     */
    public function index(): View
    {
        $user = auth()->user();
        $customers = Customer::query();

        if ($user->hasRole('admin')) {
            // Admin sees all customers
        } elseif ($user->hasRole('seo provider')) {
            // SEO provider sees only assigned customers
            $customers->whereHas('providers', function($query) use ($user) {
                $query->where('users.id', $user->id);
            });
        } else {
            // Customer sees only their own customers
            $customers->where('user_id', $user->id);
        }

        $customers = $customers->latest()->paginate(10);

        // Add a flag to indicate if this is a SEO provider view
        $isSeoProvider = $user->hasRole('seo provider');

        return view('customers.index', compact('customers', 'isSeoProvider'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $validated['user_id'] = auth()->id();

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('customer-logos', 'public');
            $validated['logo_path'] = $path;
        }

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): View
    {
        $user = auth()->user();
        
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('seo provider')) {
                // Check if the provider is assigned to this customer
                if (!$customer->providers()->where('users.id', $user->id)->exists()) {
                    abort(403);
                }
            } elseif ($customer->user_id !== $user->id) {
                abort(403);
            }
        }
        
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        if (!auth()->user()->hasRole('admin') && $customer->user_id !== auth()->id()) {
            abort(403);
        }

        $providers = $this->getAllProviders();
        $assignedProviderIds = $this->getAssignedProviderIds($customer);

        return view('customers.edit', compact('customer', 'providers', 'assignedProviderIds'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        if (!auth()->user()->hasRole('admin') && $customer->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'providers' => 'nullable|array',
            'providers.*' => 'exists:users,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($customer->logo_path) {
                Storage::disk('public')->delete($customer->logo_path);
            }
            
            $path = $request->file('logo')->store('customer-logos', 'public');
            $validated['logo_path'] = $path;
        }

        $customer->update($validated);

        // Sync providers if provided in the request
        if (isset($validated['providers'])) {
            $this->syncProviders($customer, $validated['providers']);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        if (!auth()->user()->hasRole('admin') && $customer->user_id !== auth()->id()) {
            abort(403);
        }

        // Delete logo if exists
        if ($customer->logo_path) {
            Storage::disk('public')->delete($customer->logo_path);
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
} 