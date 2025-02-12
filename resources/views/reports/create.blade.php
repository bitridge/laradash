<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate Report') }} - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form id="report-form" action="{{ route('reports.generate') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">

                        <!-- Overview Section -->
                        <div>
                            <x-input-label for="overview" :value="__('Report Overview')" />
                            <textarea id="overview" name="overview" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="Enter an overview for the report...">{{ old('overview') }}</textarea>
                            <x-input-error :messages="$errors->get('overview')" class="mt-2" />
                        </div>

                        <!-- Dynamic Sections -->
                        <div id="sections-container" class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Report Sections</h3>
                            <div class="sections space-y-4">
                                <!-- Sections will be added here -->
                            </div>
                            <button type="button" onclick="createSection()" class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Section
                            </button>
                        </div>

                        <!-- SEO Logs Selection -->
                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Select SEO Logs</h3>
                            <div class="max-h-96 overflow-y-auto border rounded-md p-4">
                                @forelse($project->seoLogs as $log)
                                    <div class="flex items-start py-2">
                                        <input type="checkbox" name="seo_logs[]" value="{{ $log->id }}" 
                                            class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <div class="ml-3">
                                            <div class="font-medium">{{ $log->title }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $log->created_at->format('M d, Y') }} | {{ $log->type_label }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500">No SEO logs found for this project.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="flex justify-end gap-4">
                            <x-secondary-button type="button" onclick="window.history.back()">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button>
                                {{ __('Generate Report') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script type="text/javascript">
        // Global variables
        const sectionsContainer = document.querySelector('.sections');
        let sectionCount = 0;

        // Function to create a new section
        function createSection() {
            console.log('Creating new section...'); // Debug log
            const section = document.createElement('div');
            section.className = 'section bg-gray-50 p-4 rounded-lg relative mb-4';
            section.dataset.priority = sectionCount + 1;

            section.innerHTML = `
                <div class="absolute right-4 top-4 flex items-center gap-2">
                    <button type="button" onclick="moveSection(this, 'up')" class="move-up text-gray-500 hover:text-gray-700">↑</button>
                    <button type="button" onclick="moveSection(this, 'down')" class="move-down text-gray-500 hover:text-gray-700">↓</button>
                    <button type="button" onclick="deleteSection(this)" class="delete-section text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="space-y-4 pt-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Section Title</label>
                        <input type="text" name="sections[${sectionCount}][title]" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Content</label>
                        <textarea name="sections[${sectionCount}][content]" rows="3" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                            required></textarea>
                    </div>
                    <input type="hidden" name="sections[${sectionCount}][priority]" value="${sectionCount + 1}">
                </div>
            `;

            sectionsContainer.appendChild(section);
            sectionCount++;
            updatePriorities();
            console.log('Section created. Total sections:', sectionCount); // Debug log
        }

        // Function to delete a section
        function deleteSection(button) {
            const section = button.closest('.section');
            section.remove();
            updatePriorities();
        }

        // Function to move a section up or down
        function moveSection(button, direction) {
            const section = button.closest('.section');
            if (direction === 'up' && section.previousElementSibling) {
                section.parentNode.insertBefore(section, section.previousElementSibling);
            } else if (direction === 'down' && section.nextElementSibling) {
                section.parentNode.insertBefore(section.nextElementSibling, section);
            }
            updatePriorities();
        }

        // Function to update priorities
        function updatePriorities() {
            const sections = document.querySelectorAll('.section');
            sections.forEach((section, index) => {
                section.dataset.priority = index + 1;
                const priorityInput = section.querySelector('input[name*="[priority]"]');
                if (priorityInput) {
                    priorityInput.value = index + 1;
                }
            });
        }

        // Create initial section when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            if (sectionsContainer && sectionsContainer.children.length === 0) {
                createSection();
            }
        });
    </script>
    @endpush
</x-app-layout> 