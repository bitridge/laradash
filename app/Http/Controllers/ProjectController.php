<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view projects')->only(['index', 'show']);
        $this->middleware('permission:create projects|edit assigned projects')->only(['create', 'store']);
        $this->middleware('permission:edit projects|edit assigned projects')->only(['edit', 'update']);
        $this->middleware('permission:delete projects')->only('destroy');
    }

    /**
     * Display a listing of the projects.
     */
    public function index(): View
    {
        $user = auth()->user();
        $projects = Project::with('customer');

        if ($user->hasRole('admin')) {
            // Admin sees all projects
        } elseif ($user->hasRole('seo provider')) {
            // SEO provider sees projects from their assigned customers
            $projects->whereHas('customer', function($query) use ($user) {
                $query->whereHas('providers', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            });
        } else {
            // Customer sees only their projects
            $projects->where('user_id', $user->id);
        }

        $projects = $projects->latest()->paginate(10);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): View
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            // Admin can see all customers
            $customers = Customer::orderBy('name')->get();
        } elseif ($user->hasRole('seo provider')) {
            // SEO provider can see assigned customers
            $customers = $user->assignedCustomers()->orderBy('name')->get();
        } else {
            // Customer can see only their own customers
            $customers = Customer::where('user_id', $user->id)->orderBy('name')->get();
        }

        return view('projects.create', compact('customers'));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:pending,in_progress,completed,on_hold',
            'customer_id' => 'required|exists:customers,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $project = new Project($validated);
        $project->user_id = auth()->id();

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('project-logos', 'public');
            $project->logo_path = $logoPath;
        }

        $project->save();

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): View
    {
        $user = auth()->user();
        
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('seo provider')) {
                // Check if the provider is assigned to this project's customer
                $isAssignedToCustomer = $project->customer->providers()
                    ->where('users.id', $user->id)
                    ->exists();
                
                if (!$isAssignedToCustomer) {
                    abort(403, 'You are not assigned to this customer.');
                }
            } elseif ($project->user_id !== $user->id) {
                abort(403);
            }
        }

        $project->load(['customer', 'seoLogs.user']);
        
        return view('projects.show', [
            'project' => $project,
            'canManageSeoLogs' => $user->hasRole('seo provider')
        ]);
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project): View
    {
        $user = auth()->user();

        if (!$user->hasRole('admin')) {
            if ($user->hasRole('seo provider')) {
                // Check if the provider is assigned to this project's customer
                $isAssignedToCustomer = $project->customer->providers()
                    ->where('users.id', $user->id)
                    ->exists();
                
                if (!$isAssignedToCustomer) {
                    abort(403, 'You are not assigned to this customer.');
                }
            } elseif ($project->user_id !== $user->id) {
                abort(403);
            }
        }

        if ($user->hasRole('admin')) {
            $customers = Customer::orderBy('name')->get();
        } elseif ($user->hasRole('seo provider')) {
            $customers = $user->assignedCustomers()->orderBy('name')->get();
        } else {
            $customers = Customer::where('user_id', $user->id)->orderBy('name')->get();
        }

        return view('projects.edit', compact('project', 'customers'));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:pending,in_progress,completed,on_hold',
            'customer_id' => 'required|exists:customers,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($project->logo_path && Storage::disk('public')->exists($project->logo_path)) {
                Storage::disk('public')->delete($project->logo_path);
            }
            
            $logoPath = $request->file('logo')->store('project-logos', 'public');
            $project->logo_path = $logoPath;
        }

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        if (!auth()->user()->hasRole('admin') && $project->user_id !== auth()->id()) {
            abort(403);
        }

        // Delete logo if exists
        if ($project->logo_path) {
            Storage::disk('public')->delete($project->logo_path);
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
} 