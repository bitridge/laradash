<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\SeoLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Cache dashboard data for 5 minutes
        $stats = Cache::remember('dashboard.stats', 300, function () {
            return $this->getStats();
        });

        $charts = Cache::remember('dashboard.charts', 300, function () {
            return $this->getChartData();
        });

        return view('dashboard', compact('stats', 'charts'));
    }

    private function getStats(): array
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return [
                'total_customers' => Customer::count(),
                'total_projects' => Project::count(),
                'total_seo_logs' => SeoLog::count(),
                'recent_activities' => SeoLog::with(['project', 'user'])
                    ->latest()
                    ->take(5)
                    ->get(),
                'projects_by_status' => Project::selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray(),
            ];
        } elseif ($user->hasRole('seo provider')) {
            return [
                'total_projects' => Project::whereHas('seoLogs', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->count(),
                'total_seo_logs' => SeoLog::where('user_id', $user->id)->count(),
                'recent_activities' => SeoLog::with(['project', 'user'])
                    ->where('user_id', $user->id)
                    ->latest()
                    ->take(5)
                    ->get(),
                'projects_by_status' => Project::whereHas('seoLogs', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            ];
        } else { // customer
            return [
                'total_projects' => Project::where('user_id', $user->id)->count(),
                'total_seo_logs' => SeoLog::whereHas('project', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->count(),
                'recent_activities' => SeoLog::with(['project', 'user'])
                    ->whereHas('project', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->latest()
                    ->take(5)
                    ->get(),
                'projects_by_status' => Project::where('user_id', $user->id)
                    ->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray(),
            ];
        }
    }

    private function getChartData(): array
    {
        $user = auth()->user();
        $startDate = Carbon::now()->subMonths(6);

        if ($user->hasRole('admin')) {
            $projectsData = Project::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray();

            $seoLogsData = SeoLog::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray();
        } elseif ($user->hasRole('seo provider')) {
            $projectsData = Project::whereHas('seoLogs', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

            $seoLogsData = SeoLog::where('user_id', $user->id)
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray();
        } else { // customer
            $projectsData = Project::where('user_id', $user->id)
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray();

            $seoLogsData = SeoLog::whereHas('project', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();
        }

        return [
            'projects' => [
                'labels' => array_keys($projectsData),
                'data' => array_values($projectsData),
            ],
            'seo_logs' => [
                'labels' => array_keys($seoLogsData),
                'data' => array_values($seoLogsData),
            ],
        ];
    }
} 