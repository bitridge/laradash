<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit SEO Log') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('projects.seo-logs.update', [$project, $seoLog]) }}" class="space-y-6" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $seoLog->title)" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Type -->
                        <div>
                            <x-input-label for="type" :value="__('Type')" />
                            <select id="type" name="type" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="analysis" {{ old('type', $seoLog->type) == 'analysis' ? 'selected' : '' }}>Analysis</option>
                                <option value="optimization" {{ old('type', $seoLog->type) == 'optimization' ? 'selected' : '' }}>Optimization</option>
                                <option value="report" {{ old('type', $seoLog->type) == 'report' ? 'selected' : '' }}>Report</option>
                                <option value="other" {{ old('type', $seoLog->type) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Content -->
                        <div>
                            <x-input-label for="content" :value="__('Content')" />
                            <textarea id="content" name="content" rows="10" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('content', $seoLog->content) }}</textarea>
                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                        </div>

                        <!-- File Upload -->
                        <div>
                            <x-input-label for="attachments" :value="__('Upload Images')" />
                            <input type="file" id="attachments" name="attachments[]" multiple accept="image/*" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100" />
                            <p class="mt-1 text-sm text-gray-500">Upload one or multiple images (PNG, JPG, GIF up to 5MB each)</p>
                            <x-input-error :messages="$errors->get('attachments')" class="mt-2" />
                        </div>

                        @if($seoLog->media->count() > 0)
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Current Images</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach($seoLog->media as $media)
                                <div class="relative group">
                                    <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}" class="w-full h-32 object-cover rounded-lg">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300 rounded-lg flex items-center justify-center">
                                        <div class="hidden group-hover:block">
                                            <label class="inline-flex items-center px-3 py-1 rounded-md bg-white text-gray-700 cursor-pointer">
                                                <input type="checkbox" name="delete_media[]" value="{{ $media->id }}" class="mr-2 rounded border-gray-300">
                                                Delete
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Check the boxes to remove images</p>
                        </div>
                        @endif

                        <div class="flex items-center justify-end mt-4">
                            <x-secondary-button onclick="window.history.back()" type="button" class="mr-3">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button>
                                {{ __('Update SEO Log') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize any JavaScript enhancements here
        document.addEventListener('DOMContentLoaded', function() {
            // Add any JavaScript functionality if needed
        });
    </script>
    @endpush
</x-app-layout> 