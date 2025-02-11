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
        $this->middleware('permission:view seo logs')->only(['index', 'show', 'allLogs']);
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
            $logs = SeoLog::with(['project', 'user'])
                ->latest()
                ->paginate(10);
        } else {
            // For other users, show logs from their projects
            $logs = SeoLog::whereIn('project_id', $user->projects()->pluck('id'))
                ->with(['project', 'user'])
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
            'type' => 'required|in:analysis,optimization,report,other',
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
     * Show the form for editing the specified SEO log.
     */
    public function edit(Project $project, SeoLog $seoLog): View
    {
        $this->authorize('update', [$seoLog, $project]);
        return view('seo-logs.edit', compact('project', 'seoLog'));
    }

    /**
     * Update the specified SEO log in storage.
     */
    public function update(Request $request, Project $project, SeoLog $seoLog): RedirectResponse
    {
        $this->authorize('update', [$seoLog, $project]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:analysis,optimization,report,other',
            'attachments' => 'nullable|array',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'delete_media' => 'nullable|array',
            'delete_media.*' => 'integer|exists:media,id'
        ]);

        $seoLog->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
        ]);

        // Handle file deletions
        if (!empty($request->delete_media)) {
            $seoLog->media()->whereIn('id', $request->delete_media)->delete();
        }

        // Handle new file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $image) {
                $seoLog->addMedia($image)
                    ->toMediaCollection('attachments');
            }
        }

        return redirect()->route('projects.seo-logs.show', [$project, $seoLog])
            ->with('success', 'SEO log updated successfully.');
    }

    /**
     * Remove the specified SEO log from storage.
     */
    public function destroy(Project $project, SeoLog $seoLog): RedirectResponse
    {
        $this->authorize('delete', [$seoLog, $project]);

        // Delete associated media
        $seoLog->media()->delete();
        
        $seoLog->delete();

        return redirect()->route('projects.seo-logs.index', $project)
            ->with('success', 'SEO log deleted successfully.');
    }
} 