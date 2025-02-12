<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Report;
use App\Models\SeoLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:generate reports');
    }

    public function index()
    {
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            $reports = Report::with(['project.customer', 'user'])
                ->latest('generated_at')
                ->paginate(10);
        } elseif ($user->hasRole('seo provider')) {
            $reports = Report::whereHas('project', function ($query) use ($user) {
                $query->whereHas('customer', function ($q) use ($user) {
                    $q->whereHas('providers', function ($p) use ($user) {
                        $p->where('users.id', $user->id);
                    });
                });
            })
            ->with(['project.customer', 'user'])
            ->latest('generated_at')
            ->paginate(10);
        } else {
            $reports = Report::whereHas('project', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['project.customer', 'user'])
            ->latest('generated_at')
            ->paginate(10);
        }

        return view('reports.index', compact('reports'));
    }

    public function create(Project $project)
    {
        return view('reports.create', compact('project'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
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
            'title' => $request->title,
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
        
        // Store the PDF file
        $fileName = "seo-report-{$project->id}-" . time() . ".pdf";
        $filePath = "reports/{$fileName}";
        Storage::disk('public')->put($filePath, $pdf->output());

        // Create report record
        Report::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'title' => $request->title,
            'overview' => $request->overview,
            'sections' => $sections,
            'included_logs' => $request->seo_logs,
            'file_path' => $filePath,
            'generated_at' => now(),
        ]);

        return $pdf->download($fileName);
    }

    public function download(Report $report)
    {
        // Check if user has access to this report
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('seo provider')) {
                $hasAccess = $report->project->customer->providers()
                    ->where('users.id', $user->id)
                    ->exists();
                
                if (!$hasAccess) {
                    abort(403);
                }
            } elseif ($report->project->user_id !== $user->id) {
                abort(403);
            }
        }

        return Storage::disk('public')->download($report->file_path);
    }
} 