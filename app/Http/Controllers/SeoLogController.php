<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SeoLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SeoLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view seo logs')->only(['index', 'show']);
        $this->middleware('permission:create seo logs')->only(['create', 'store']);
        $this->middleware('permission:edit seo logs')->only(['edit', 'update']);
        $this->middleware('permission:delete seo logs')->only('destroy');
    }

    /**
     * Display a listing of all SEO logs.
     */
    public function allLogs(): View
    {
        $user = auth()->user();
        
        // If user is admin, show all logs
        if ($user->hasRole('admin')) {
            $logs = SeoLog::with(['project.customer', 'user'])
                ->latest()
                ->paginate(10);
        } elseif ($user->hasRole('seo provider')) {
            // For SEO providers, show logs from their assigned customers' projects
            $logs = SeoLog::whereHas('project', function ($query) use ($user) {
                $query->whereHas('customer', function ($q) use ($user) {
                    $q->whereHas('providers', function ($p) use ($user) {
                        $p->where('users.id', $user->id);
                    });
                });
            })
            ->with(['project.customer', 'user'])
            ->latest()
            ->paginate(10);
        } else {
            // For customers, show logs from their projects
            $logs = SeoLog::whereHas('project', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['project.customer', 'user'])
            ->latest()
            ->paginate(10);
        }

        return view('seo-logs.all', compact('logs'));
    }

    /**
     * Display a listing of the SEO logs.
     */
    public function index(Project $project): View
    {
        $this->authorize('viewAny', [SeoLog::class, $project]);

        $logs = $project->seoLogs()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('seo-logs.index', compact('project', 'logs'));
    }

    /**
     * Show the form for creating a new SEO log.
     */
    public function create(Project $project): View
    {
        $this->authorize('create', [SeoLog::class, $project]);

        return view('seo-logs.create', compact('project'));
    }

    /**
     * Store a newly created SEO log in storage.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('create', [SeoLog::class, $project]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:seo_analytics_reporting,technical_seo,on_page_seo,off_page_seo,local_seo,content_seo',
            'attachments' => 'nullable|array',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $seoLog = SeoLog::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'user_id' => auth()->id(),
            'project_id' => $project->id,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $image) {
                $seoLog->addMedia($image)
                    ->toMediaCollection('attachments');
            }
        }

        return redirect()->route('projects.seo-logs.index', $project)
            ->with('success', 'SEO log created successfully.');
    }

    /**
     * Display the specified SEO log.
     */
    public function show(Project $project, SeoLog $seoLog): View
    {
        $this->authorize('view', [$seoLog, $project]);

        return view('seo-logs.show', compact('project', 'seoLog'));
    }

    /**
     * Check if the user can access the project
     */
    private function checkProjectAccess(Project $project)
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
    }

    /**
     * Check if the user can modify the SEO log
     */
    private function checkSeoLogAccess(SeoLog $seoLog)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('admin') && $seoLog->user_id !== $user->id) {
            abort(403, 'You can only modify your own SEO logs.');
        }
    }

    /**
     * Show the form for editing the specified SEO log.
     */
    public function edit(Project $project, SeoLog $seoLog): View
    {
        $this->checkProjectAccess($project);
        $this->checkSeoLogAccess($seoLog);

        return view('seo-logs.edit', compact('project', 'seoLog'));
    }

    /**
     * Update the specified SEO log in storage.
     */
    public function update(Request $request, Project $project, SeoLog $seoLog): RedirectResponse
    {
        $this->checkProjectAccess($project);
        $this->checkSeoLogAccess($seoLog);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:seo_analytics_reporting,technical_seo,on_page_seo,off_page_seo,local_seo,content_seo',
            'meta_data' => 'nullable|array'
        ]);

        $seoLog->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'SEO log updated successfully.');
    }

    /**
     * Remove the specified SEO log from storage.
     */
    public function destroy(Project $project, SeoLog $seoLog): RedirectResponse
    {
        $this->checkProjectAccess($project);
        $this->checkSeoLogAccess($seoLog);

        $seoLog->delete();

        return redirect()->route('projects.show', $project)
            ->with('success', 'SEO log deleted successfully.');
    }
} 