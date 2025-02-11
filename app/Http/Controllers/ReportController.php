<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SeoLog;
use Illuminate\Http\Request;
use PDF;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $projects = Project::with('seoLogs')->get();
        return view('reports.index', compact('projects'));
    }

    public function create(Project $project)
    {
        return view('reports.create', compact('project'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'overview' => 'required|string',
            'sections' => 'required|array',
            'sections.*.title' => 'required|string',
            'sections.*.content' => 'required|string',
            'sections.*.priority' => 'required|integer',
            'seo_logs' => 'required|array',
            'seo_logs.*' => 'exists:seo_logs,id'
        ]);

        $project = Project::with(['customer', 'seoLogs.user', 'seoLogs.media'])->findOrFail($request->project_id);
        $seoLogs = SeoLog::with(['user', 'media'])->whereIn('id', $request->seo_logs)->get();

        // Sort sections by priority
        $sections = collect($request->sections)->sortBy('priority')->values()->all();

        $data = [
            'project' => $project,
            'overview' => $request->overview,
            'sections' => $sections,
            'seoLogs' => $seoLogs,
            'generatedAt' => now()
        ];

        // If preview requested, return the HTML view
        if ($request->has('preview')) {
            return view('reports.preview', $data);
        }

        // Generate PDF
        $pdf = PDF::loadView('reports.pdf', $data);
        return $pdf->download("seo-report-{$project->id}.pdf");
    }
} 