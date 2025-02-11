<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Project Details') }}
            </h2>
            <a href="{{ route('projects.edit', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Edit Project
            </a>
        </div>
    </x-slot>

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
                                    <span class="ml-2">{{ $project->customer->name }}</span>
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

                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900">SEO Logs</h3>
                        <div class="mt-4">
                            <p class="text-gray-500">SEO logs will be implemented in the next phase.</p>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <x-secondary-button onclick="window.history.back()" type="button">
                            {{ __('Back') }}
                        </x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 