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
                    <form id="report-form" action="{{ route('reports.generate') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Report Title')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Overview Section -->
                        <div>
                            <x-input-label for="overview" :value="__('Report Overview')" />
                            <textarea id="overview" name="overview" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="Enter an overview for the report...">{{ old('overview') }}</textarea>
                            <x-input-error :messages="$errors->get('overview')" class="mt-2" />
                        </div>

                        <!-- Dynamic Sections -->
                        <div id="sections-container" class="space-y-6">
                            <template id="section-template">
                                <div class="section bg-gray-50 p-4 rounded-lg relative">
                                    <div class="absolute top-2 right-2 flex space-x-2">
                                        <button type="button" onclick="moveSection(this, 'up')" class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="moveSection(this, 'down')" class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="removeSection(this)" class="text-red-400 hover:text-red-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="space-y-4">
                                        <input type="hidden" name="sections[][priority]" value="1">
                                        <div>
                                            <x-input-label :value="__('Section Title')" />
                                            <x-text-input name="sections[][title]" type="text" class="mt-1 block w-full" required />
                                        </div>
                                        <div>
                                            <x-input-label :value="__('Section Content')" />
                                            <textarea name="sections[][content]" rows="4"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                required></textarea>
                                        </div>
                                        <div>
                                            <x-input-label :value="__('Section Screenshot')" />
                                            <input type="file" name="sections[][image]" accept="image/*"
                                                class="mt-1 block w-full text-sm text-gray-500
                                                file:mr-4 file:py-2 file:px-4
                                                file:rounded-md file:border-0
                                                file:text-sm file:font-semibold
                                                file:bg-indigo-50 file:text-indigo-700
                                                hover:file:bg-indigo-100">
                                            <p class="mt-1 text-sm text-gray-500">Optional. Upload a screenshot for this section (PNG, JPG up to 5MB)</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-4">
                            <x-secondary-button type="button" onclick="createSection()">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Section
                            </x-secondary-button>
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
        const sectionsContainer = document.getElementById('sections-container');
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
                    <div>
                        <x-input-label :value="__('Section Screenshot')" />
                        <input type="file" name="sections[${sectionCount}][image]" accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100">
                        <p class="mt-1 text-sm text-gray-500">Optional. Upload a screenshot for this section (PNG, JPG up to 5MB)</p>
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

        // Function to move sections up or down
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
            document.querySelectorAll('.section').forEach((section, index) => {
                section.dataset.priority = index + 1;
                section.querySelector('input[name$="[priority]"]').value = index + 1;
            });
        }

        // Form submission validation
        document.getElementById('report-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if at least one section exists
            if (document.querySelectorAll('.section').length === 0) {
                alert('Please add at least one section to the report.');
                return;
            }

            // Check if at least one SEO log is selected
            const selectedLogs = document.querySelectorAll('input[name="seo_logs[]"]:checked');
            if (selectedLogs.length === 0) {
                alert('Please select at least one SEO log to include in the report.');
                return;
            }

            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg> Generating...`;

            // If all validations pass, submit the form
            this.submit();
        });

        // Create initial section
        createSection();
    </script>
    @endpush
</x-app-layout> 
