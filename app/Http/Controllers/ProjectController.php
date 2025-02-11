<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view projects')->only(['index', 'show']);
        $this->middleware('permission:create projects')->only(['create', 'store']);
        $this->middleware('permission:edit projects')->only(['edit', 'update']);
        $this->middleware('permission:delete projects')->only('destroy');
    }

    /**
     * Display a listing of the projects.
     */
    public function index(): View
    {
        $projects = Project::with('customer');

        // If user is not an admin or SEO provider, only show their projects
        if (!auth()->user()->hasAnyRole(['admin', 'seo provider'])) {
            $projects->where('user_id', auth()->id());
        }

        $projects = $projects->latest()->paginate(10);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): View
    {
        $customers = Customer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('projects.create', compact('customers'));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:pending,in_progress,completed,on_hold',
            'customer_id' => 'required|exists:customers,id',
        ]);

        $validated['user_id'] = auth()->id();

        // Verify the customer belongs to the authenticated user
        $customer = Customer::findOrFail($validated['customer_id']);
        if ($customer->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): View
    {
        if (!auth()->user()->hasRole(['admin', 'seo provider']) && $project->user_id !== auth()->id()) {
            abort(403);
        }
        
        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project): View
    {
        if (!auth()->user()->hasRole('admin') && $project->user_id !== auth()->id()) {
            abort(403);
        }

        $customers = Customer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('projects.edit', compact('project', 'customers'));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        if (!auth()->user()->hasRole('admin') && $project->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:pending,in_progress,completed,on_hold',
            'customer_id' => 'required|exists:customers,id',
        ]);

        // Verify the customer belongs to the authenticated user
        $customer = Customer::findOrFail($validated['customer_id']);
        if ($customer->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $project->update($validated);

        return redirect()->route('projects.index')
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

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
} 