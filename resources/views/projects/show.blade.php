<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Project Details') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('reports.create', $project) }}" 
                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Generate Report
                </a>
                <a href="{{ route('projects.edit', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Project
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Report Preview Modal -->
    <div id="report-preview-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" role="dialog">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-4/5 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold">Report Preview</h3>
                <button id="close-modal" class="text-black close-modal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="report-preview-content" class="mt-4">
                <!-- Report content will be loaded here -->
            </div>
            <div class="flex justify-end gap-4 mt-4">
                <button id="download-report" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Download PDF
                </button>
                <button class="close-modal bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Close
                </button>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Project Information</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <span class="text-gray-500">Name:</span>
                                    <span class="ml-2">{{ $project->name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Customer:</span>
                                    <span class="ml-2">{{ $project->customer?->name ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Status:</span>
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $project->status_color }}-100 text-{{ $project->status_color }}-800">
                                        {{ $project->status_label }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Description:</span>
                                    <p class="mt-1">{{ $project->description ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Timeline</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <span class="text-gray-500">Start Date:</span>
                                    <span class="ml-2">{{ $project->start_date->format('M d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">End Date:</span>
                                    <span class="ml-2">{{ $project->end_date ? $project->end_date->format('M d, Y') : 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Created:</span>
                                    <span class="ml-2">{{ $project->created_at->format('M d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Last Updated:</span>
                                    <span class="ml-2">{{ $project->updated_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Actions -->
                    <div class="mt-6 flex space-x-3">
                        @if($canManageSeoLogs)
                            <a href="{{ route('projects.seo-logs.create', $project) }}" class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add SEO Log
                            </a>
                            <a href="{{ route('projects.seo-logs.index', $project) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                View Reports
                            </a>
                        @endif
                        @can('edit projects')
                            <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white font-semibold rounded-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Project
                            </a>
                        @endcan
                    </div>

                    <!-- SEO Logs Section -->
                    @if($canManageSeoLogs || count($project->seoLogs) > 0)
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO Logs</h3>
                            @if(count($project->seoLogs) > 0)
                                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                    <ul class="divide-y divide-gray-200">
                                        @foreach($project->seoLogs->sortByDesc('created_at') as $log)
                                            <li class="px-4 py-4 sm:px-6">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <div class="flex justify-between items-start">
                                                            <h4 class="text-sm font-medium text-indigo-600">{{ $log->title }}</h4>
                                                            @if($canManageSeoLogs && $log->user_id === auth()->id())
                                                                <div class="flex space-x-2">
                                                                    <a href="{{ route('projects.seo-logs.edit', [$project, $log]) }}" 
                                                                       class="text-indigo-600 hover:text-indigo-900">
                                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                            </path>
                                                                        </svg>
                                                                    </a>
                                                                    <form action="{{ route('projects.seo-logs.destroy', [$project, $log]) }}" 
                                                                          method="POST" 
                                                                          class="inline"
                                                                          onsubmit="return confirm('Are you sure you want to delete this SEO log?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                                </path>
                                                                            </svg>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="mt-2 text-sm text-gray-500 prose max-w-none">
                                                            {!! $log->content !!}
                                                        </div>
                                                    </div>
                                                    <div class="ml-4 flex-shrink-0">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $log->type_color }}-100 text-{{ $log->type_color }}-800">
                                                            {{ $log->type }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="mt-2 text-xs text-gray-500">
                                                    By {{ $log->user->name }} • {{ $log->created_at->diffForHumans() }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <p class="text-gray-500">No SEO logs have been added yet.</p>
                            @endif
                        </div>
                    @endif

                    <div class="mt-8 flex justify-end">
                        <x-secondary-button onclick="window.history.back()" type="button">
                            {{ __('Back') }}
                        </x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('report-preview-modal');
            const previewBtn = document.getElementById('preview-report-btn');
            const closeButtons = document.querySelectorAll('.close-modal');
            const downloadBtn = document.getElementById('download-report');
            const form = document.getElementById('generate-report-form');
            const previewContent = document.getElementById('report-preview-content');

            // Show modal with preview
            previewBtn.addEventListener('click', function() {
                // Show loading state
                previewContent.innerHTML = '<div class="flex justify-center"><div class="loader">Loading...</div></div>';
                modal.classList.remove('hidden');

                // Fetch preview content
                const formData = new FormData(form);
                formData.append('preview', 'true');

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'text/html'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    previewContent.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    previewContent.innerHTML = '<div class="text-red-500 p-4">Error loading preview: ' + error.message + '</div>';
                });
            });

            // Close modal when clicking close buttons
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    modal.classList.add('hidden');
                });
            });

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });

            // Download PDF
            downloadBtn.addEventListener('click', function() {
                const formData = new FormData(form);
                formData.append('download', 'true');
                
                const tempForm = document.createElement('form');
                tempForm.method = 'POST';
                tempForm.action = form.action;
                
                // Add CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                tempForm.appendChild(csrfInput);
                
                for (const [key, value] of formData.entries()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    tempForm.appendChild(input);
                }
                
                document.body.appendChild(tempForm);
                tempForm.submit();
                document.body.removeChild(tempForm);
            });

            // Handle escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            });
        });
    </script>

    <style>
        .loader {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #report-preview-modal {
            z-index: 50;
        }

        #report-preview-content {
            max-height: calc(100vh - 250px);
            overflow-y: auto;
        }
    </style>
    @endpush
</x-app-layout> 