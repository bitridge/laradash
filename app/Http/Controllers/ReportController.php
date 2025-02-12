<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Report;
use App\Models\SeoLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        try {
            Log::info('Starting report generation process', ['request' => $request->all()]);

            $request->validate([
                'project_id' => 'required|exists:projects,id',
                'title' => 'required|string|max:255',
                'overview' => 'required|string',
                'sections' => 'required|array',
                'sections.*.title' => 'required|string',
                'sections.*.content' => 'required|string',
                'sections.*.priority' => 'required|integer',
                'sections.*.image' => 'nullable|image|max:5120',
                'seo_logs' => 'required|array',
                'seo_logs.*' => 'exists:seo_logs,id'
            ]);

            Log::info('Validation passed');

            $project = Project::with(['customer', 'seoLogs.user', 'seoLogs.media'])->findOrFail($request->project_id);
            $seoLogs = SeoLog::with(['user', 'media'])->whereIn('id', $request->seo_logs)->get();

            Log::info('Project and SEO logs loaded', [
                'project_id' => $project->id,
                'seo_logs_count' => $seoLogs->count()
            ]);

            // Sort sections by priority and process images
            $sections = collect($request->sections)->map(function ($section, $index) use ($project) {
                $processedSection = [
                    'title' => $section['title'],
                    'content' => $section['content'],
                    'priority' => $section['priority']
                ];

                // Handle image upload if present
                if (isset($section['image']) && $section['image']->isValid()) {
                    Log::info('Processing image for section', ['section_title' => $section['title']]);
                    $path = $section['image']->store('report-screenshots/' . $project->id, 'public');
                    $processedSection['image_path'] = public_path('storage/' . $path);
                    Log::info('Image processed', ['path' => $processedSection['image_path']]);
                }

                return $processedSection;
            })->sortBy('priority')->values()->all();

            Log::info('Sections processed', ['sections_count' => count($sections)]);

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
                Log::info('Returning preview');
                return view('reports.preview', $data);
            }

            Log::info('Starting PDF generation');

            // Generate PDF with additional options for images
            $pdf = PDF::loadView('reports.pdf', $data);
            
            // Configure PDF settings
            $pdf->setPaper('a4');
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('encoding', 'UTF-8');
            $pdf->setOption('margin-top', '20');
            $pdf->setOption('margin-right', '20');
            $pdf->setOption('margin-bottom', '20');
            $pdf->setOption('margin-left', '20');
            $pdf->setOption('images', true);
            $pdf->setOption('isRemoteEnabled', true);
            
            Log::info('PDF options configured');

            // Store the PDF file
            $fileName = "seo-report-{$project->id}-" . time() . ".pdf";
            $filePath = "reports/{$fileName}";
            Storage::disk('public')->put($filePath, $pdf->output());

            Log::info('PDF file stored', ['file_path' => $filePath]);

            // Create report record
            $report = Report::create([
                'project_id' => $project->id,
                'user_id' => auth()->id(),
                'title' => $request->title,
                'overview' => $request->overview,
                'sections' => $sections,
                'included_logs' => $request->seo_logs,
                'file_path' => $filePath,
                'generated_at' => now(),
            ]);

            Log::info('Report record created', ['report_id' => $report->id]);

            // Return the PDF for download
            return response()->download(storage_path('app/public/' . $filePath), $fileName, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to generate report: ' . $e->getMessage());
        }
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