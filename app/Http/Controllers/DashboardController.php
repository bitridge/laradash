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
            // Get assigned customers with their projects and SEO logs
            $assignedCustomers = $user->assignedCustomers()
                ->with(['projects' => function ($query) {
                    $query->withCount('seoLogs')
                        ->latest();
                }, 'projects.seoLogs' => function ($query) {
                    $query->with('user')
                        ->latest()
                        ->take(5);
                }])
                ->get();

            // Get all SEO logs from assigned customers' projects
            $allSeoLogs = SeoLog::whereHas('project', function ($query) use ($user) {
                $query->whereHas('customer', function ($q) use ($user) {
                    $q->whereHas('providers', function ($p) use ($user) {
                        $p->where('users.id', $user->id);
                    });
                });
            });

            return [
                'total_customers' => $assignedCustomers->count(),
                'total_projects' => $assignedCustomers->sum(function ($customer) {
                    return $customer->projects->count();
                }),
                'total_seo_logs' => $allSeoLogs->count(),
                'recent_activities' => $allSeoLogs->with(['project.customer', 'user'])
                    ->latest()
                    ->take(5)
                    ->get(),
                'projects_by_status' => Project::whereHas('customer', function ($query) use ($user) {
                    $query->whereHas('providers', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
                })
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
                'assigned_customers' => $assignedCustomers,
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
            $projectsData = Project::whereHas('customer', function ($query) use ($user) {
                $query->whereHas('providers', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

            $seoLogsData = SeoLog::whereHas('project', function ($query) use ($user) {
                $query->whereHas('customer', function ($q) use ($user) {
                    $q->whereHas('providers', function ($p) use ($user) {
                        $p->where('users.id', $user->id);
                    });
                });
            })
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