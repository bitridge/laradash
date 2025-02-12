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

    @if(auth()->user()->hasRole('seo provider') && isset($stats['assigned_customers']))
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Assigned Customers</h2>

                        @forelse($stats['assigned_customers'] as $customer)
                            <div class="mb-8 bg-gray-50 rounded-lg p-6">
                                <!-- Customer Header -->
                                <div class="flex items-center mb-4">
                                    <img src="{{ $customer['logo_url'] }}" alt="{{ $customer['name'] }} Logo" class="w-12 h-12 rounded-full object-cover mr-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-800">{{ $customer['name'] }}</h3>
                                        <div class="text-sm text-gray-600">
                                            <p>{{ $customer['company_name'] }}</p>
                                            <p>{{ $customer['email'] }} | {{ $customer['phone'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Projects -->
                                <div class="space-y-4">
                                    @foreach($customer['projects'] as $project)
                                        <div class="bg-white rounded-lg shadow p-4">
                                            <div class="flex justify-between items-start mb-4">
                                                <div>
                                                    <h4 class="text-lg font-semibold text-gray-800">{{ $project['name'] }}</h4>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $project['status_color'] }}-100 text-{{ $project['status_color'] }}-800">
                                                        {{ $project['status'] }}
                                                    </span>
                                                </div>
                                                <a href="{{ route('projects.show', $project['id']) }}" class="text-blue-600 hover:text-blue-800">View Project</a>
                                            </div>

                                            <!-- SEO Logs -->
                                            <div class="mt-4">
                                                <h5 class="text-sm font-medium text-gray-700 mb-2">Recent SEO Logs ({{ $project['seo_logs_count'] }} total)</h5>
                                                <div class="space-y-2">
                                                    @foreach($project['seo_logs']->take(3) as $log)
                                                        <div class="flex items-center justify-between text-sm">
                                                            <div class="flex items-center space-x-2">
                                                                <span class="font-medium">{{ $log['title'] }}</span>
                                                                <span class="text-gray-500">{{ $log['type'] }}</span>
                                                                @if($log['has_attachments'])
                                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                                    </svg>
                                                                @endif
                                                            </div>
                                                            <div class="text-gray-500">{{ $log['created_at'] }}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @if($project['seo_logs_count'] > 3)
                                                    <div class="mt-2 text-right">
                                                        <a href="{{ route('projects.seo-logs.index', $project['id']) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                                            View all logs →
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <p>No customers are currently assigned to you.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

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
    </script>
    @endpush
</x-app-layout>
