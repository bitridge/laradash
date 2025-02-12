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
                                        Customer: {{ $activity->project->customer->name }} |
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

    @role('seo provider')
    <!-- Assigned Customers Section -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Assigned Customers</h3>
                    
                    @foreach($stats['assigned_customers'] as $customer)
                    <div class="mb-8 last:mb-0">
                        <!-- Customer Header -->
                        <div class="flex items-center justify-between bg-gray-50 p-4 rounded-t-lg border border-gray-200">
                            <div class="flex items-center space-x-4">
                                <img src="{{ $customer->logo_url }}" alt="{{ $customer->name }}" class="w-12 h-12 rounded-full object-cover">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900">{{ $customer->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $customer->company_name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">{{ $customer->email }}</p>
                                <p class="text-sm text-gray-500">{{ $customer->phone }}</p>
                            </div>
                        </div>

                        <!-- Projects List -->
                        <div class="border-x border-b border-gray-200 rounded-b-lg divide-y divide-gray-200">
                            @foreach($customer->projects as $project)
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h5 class="text-md font-medium text-gray-900">
                                            <a href="{{ route('projects.show', $project) }}" class="hover:text-blue-600">
                                                {{ $project->name }}
                                            </a>
                                        </h5>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $project->status_color }}-100 text-{{ $project->status_color }}-800">
                                            {{ $project->status_label }}
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('projects.seo-logs.create', $project) }}" 
                                           class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Add SEO Log
                                        </a>
                                        <a href="{{ route('projects.show', $project) }}" 
                                           class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            View Details
                                        </a>
                                    </div>
                                </div>

                                <!-- Recent SEO Logs -->
                                <div class="mt-2">
                                    <h6 class="text-sm font-medium text-gray-700 mb-2">Recent SEO Logs ({{ $project->seo_logs_count }} total)</h6>
                                    <div class="space-y-2">
                                        @forelse($project->seoLogs as $log)
                                            <div class="bg-gray-50 p-3 rounded-md">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $log->title }}</p>
                                                        <p class="text-xs text-gray-500">
                                                            By {{ $log->user->name }} • 
                                                            {{ $log->created_at->diffForHumans() }}
                                                        </p>
                                                    </div>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $log->type_color }}-100 text-{{ $log->type_color }}-800">
                                                        {{ $log->type }}
                                                    </span>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500">No SEO logs yet</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endrole

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
