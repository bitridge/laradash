<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('reports.generate') }}" class="space-y-6">
                        @csrf

                        <!-- Project Selection -->
                        <div>
                            <x-input-label for="project_id" :value="__('Select Project')" />
                            <select id="project_id" name="project_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" x-on:change="fetchSeoLogs($event.target.value)">
                                <option value="">Choose a project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
                        </div>

                        <!-- SEO Logs Selection -->
                        <div id="seo-logs-container" class="hidden">
                            <x-input-label :value="__('Select SEO Logs to Include')" class="mb-4" />
                            <div class="space-y-4" id="seo-logs-list">
                                <!-- SEO logs will be loaded here dynamically -->
                            </div>
                            <x-input-error :messages="$errors->get('seo_logs')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Generate PDF Report') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function fetchSeoLogs(projectId) {
            if (!projectId) {
                document.getElementById('seo-logs-container').classList.add('hidden');
                return;
            }

            const container = document.getElementById('seo-logs-list');
            container.innerHTML = 'Loading...';
            document.getElementById('seo-logs-container').classList.remove('hidden');

            fetch(`/api/projects/${projectId}/seo-logs`)
                .then(response => response.json())
                .then(logs => {
                    if (logs.length === 0) {
                        container.innerHTML = '<p class="text-gray-500">No SEO logs found for this project.</p>';
                        return;
                    }

                    container.innerHTML = logs.map(log => `
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   id="log-${log.id}" 
                                   name="seo_logs[]" 
                                   value="${log.id}"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="log-${log.id}" class="flex-1">
                                <div class="font-medium text-gray-900">${log.title}</div>
                                <div class="text-sm text-gray-500">
                                    ${new Date(log.created_at).toLocaleDateString()} - ${log.type}
                                </div>
                            </label>
                        </div>
                    `).join('');
                })
                .catch(error => {
                    container.innerHTML = '<p class="text-red-500">Error loading SEO logs. Please try again.</p>';
                });
        }
    </script>
    @endpush
</x-app-layout> 