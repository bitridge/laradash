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

    public function generate(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'seo_logs' => 'required|array',
            'seo_logs.*' => 'exists:seo_logs,id'
        ]);

        $project = Project::findOrFail($request->project_id);
        $seoLogs = SeoLog::whereIn('id', $request->seo_logs)->get();

        $pdf = PDF::loadView('reports.pdf', [
            'project' => $project,
            'seoLogs' => $seoLogs,
            'generatedAt' => now()
        ]);

        return $pdf->download("seo-report-{$project->id}.pdf");
    }
} 