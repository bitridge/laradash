<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- Statistics Section -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @role('admin')
                        <div class="bg-blue-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-blue-700">Total Customers</h3>
                            <p class="text-3xl font-bold text-blue-900">{{ $stats['total_customers'] }}</p>
                        </div>
                        @endrole
                        
                        <div class="bg-green-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-green-700">Total Projects</h3>
                            <p class="text-3xl font-bold text-green-900">{{ $stats['total_projects'] }}</p>
                        </div>
                        
                        <div class="bg-purple-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-purple-700">Total SEO Logs</h3>
                            <p class="text-3xl font-bold text-purple-900">{{ $stats['total_seo_logs'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Projects Chart -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Projects Over Time</h3>
                            <canvas id="projectsChart"></canvas>
                        </div>

                        <!-- SEO Logs Chart -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO Activities Over Time</h3>
                            <canvas id="seoLogsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Status Distribution -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Status Distribution</h3>
                    <canvas id="projectStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activities</h3>
                    <div class="space-y-4">
                        @foreach($stats['recent_activities'] as $activity)
                            <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $activity->title }}</p>
                                    <p class="text-sm text-gray-500">
                                        Project: {{ $activity->project->name }} |
                                        By: {{ $activity->user->name }} |
                                        {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Projects Chart
        new Chart(document.getElementById('projectsChart'), {
            type: 'line',
            data: {
                labels: @json($charts['projects']['labels']),
                datasets: [{
                    label: 'Projects',
                    data: @json($charts['projects']['data']),
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // SEO Logs Chart
        new Chart(document.getElementById('seoLogsChart'), {
            type: 'line',
            data: {
                labels: @json($charts['seo_logs']['labels']),
                datasets: [{
                    label: 'SEO Activities',
                    data: @json($charts['seo_logs']['data']),
                    borderColor: 'rgb(139, 92, 246)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Project Status Distribution Chart
        new Chart(document.getElementById('projectStatusChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(@json($stats['projects_by_status'])).map(status => 
                    status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')
                ),
                datasets: [{
                    data: Object.values(@json($stats['projects_by_status'])),
                    backgroundColor: [
                        'rgb(59, 130, 246)', // blue
                        'rgb(16, 185, 129)', // green
                        'rgb(239, 68, 68)',  // red
                        'rgb(245, 158, 11)'  // yellow
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
