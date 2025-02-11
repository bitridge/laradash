<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $seoLog->title }}
            </h2>
            <div class="flex space-x-4">
                @can('update', [$seoLog, $project])
                <x-secondary-button href="{{ route('projects.seo-logs.edit', [$project, $seoLog]) }}">
                    {{ __('Edit') }}
                </x-secondary-button>
                @endcan
                @can('delete', [$seoLog, $project])
                <form method="POST" action="{{ route('projects.seo-logs.destroy', [$project, $seoLog]) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <x-danger-button type="submit" onclick="return confirm('Are you sure you want to delete this log?')">
                        {{ __('Delete') }}
                    </x-danger-button>
                </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Metadata -->
                    <div class="mb-6">
                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $seoLog->created_at->format('M d, Y H:i') }}
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                {{ ucfirst($seoLog->type) }}
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                {{ $seoLog->user->name }}
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="prose max-w-none mb-6">
                        {!! $seoLog->content !!}
                    </div>

                    <!-- Attachments -->
                    @if($seoLog->media->count() > 0)
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Attachments') }}</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($seoLog->media as $media)
                            <div class="relative group">
                                @if(str_contains($media->mime_type, 'image'))
                                    <a href="{{ $media->getUrl() }}" target="_blank" class="block">
                                        <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}" class="w-full h-40 object-cover rounded-lg shadow-sm">
                                    </a>
                                @else
                                    <a href="{{ $media->getUrl() }}" target="_blank" class="block p-4 bg-gray-50 rounded-lg shadow-sm">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="mt-2 text-sm text-gray-600 text-center">{{ $media->name }}</span>
                                        </div>
                                    </a>
                                @endif
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-opacity rounded-lg flex items-center justify-center">
                                    <div class="hidden group-hover:block">
                                        <a href="{{ $media->getUrl() }}" download class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            {{ __('Download') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/typography@0.5.0/dist/typography.min.css">
    <style>
        .prose img {
            margin: 0 auto;
        }
    </style>
    @endpush
</x-app-layout> 