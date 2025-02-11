<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit SEO Log for') }} {{ $project->name }}
        </h2>
    </x-slot>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <link href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" rel="stylesheet">
    <style>
        .dropzone {
            border: 2px dashed #ddd;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            background: #f8fafc;
        }
        .dropzone .dz-preview .dz-image {
            border-radius: 4px;
        }
        .existing-files {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .existing-file {
            position: relative;
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
        }
        .existing-file img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 0.25rem;
        }
        .existing-file .delete-file {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            padding: 0.25rem;
            cursor: pointer;
        }
    </style>
    @endpush

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('projects.seo-logs.update', [$project, $log]) }}" class="space-y-6" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $log->title)" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Type -->
                        <div>
                            <x-input-label for="type" :value="__('Type')" />
                            <select id="type" name="type" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="analysis" {{ old('type', $log->type) == 'analysis' ? 'selected' : '' }}>Analysis</option>
                                <option value="optimization" {{ old('type', $log->type) == 'optimization' ? 'selected' : '' }}>Optimization</option>
                                <option value="report" {{ old('type', $log->type) == 'report' ? 'selected' : '' }}>Report</option>
                                <option value="other" {{ old('type', $log->type) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Content -->
                        <div>
                            <x-input-label for="content" :value="__('Content')" />
                            <textarea id="content" name="content" class="summernote">{{ old('content', $log->content) }}</textarea>
                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                        </div>

                        <!-- Existing Files -->
                        @if($log->media->count() > 0)
                        <div>
                            <x-input-label :value="__('Existing Attachments')" class="mb-2" />
                            <div class="existing-files">
                                @foreach($log->media as $media)
                                <div class="existing-file">
                                    @if(str_contains($media->mime_type, 'image'))
                                        <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}">
                                    @else
                                        <div class="p-4 text-center">
                                            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="block mt-1 text-sm">{{ $media->name }}</span>
                                        </div>
                                    @endif
                                    <div class="delete-file" onclick="toggleFileDelete({{ $media->id }})">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                    <input type="checkbox" name="delete_media[]" value="{{ $media->id }}" class="hidden delete-media-{{ $media->id }}">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- File Attachments -->
                        <div>
                            <x-input-label :value="__('Add New Attachments')" class="mb-2" />
                            <div id="dropzone" class="dropzone"></div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-secondary-button onclick="window.history.back()" type="button" class="mr-3">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button>
                                {{ __('Update Log') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script>
        function toggleFileDelete(mediaId) {
            const checkbox = document.querySelector(`.delete-media-${mediaId}`);
            const fileDiv = checkbox.closest('.existing-file');
            checkbox.checked = !checkbox.checked;
            fileDiv.style.opacity = checkbox.checked ? '0.5' : '1';
        }

        $(document).ready(function() {
            // Initialize Summernote
            $('.summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            // Initialize Dropzone
            Dropzone.autoDiscover = false;
            new Dropzone("#dropzone", {
                url: "{{ route('projects.seo-logs.update', [$project, $log]) }}",
                autoProcessQueue: false,
                uploadMultiple: true,
                parallelUploads: 5,
                maxFiles: 5,
                maxFilesize: 5, // MB
                acceptedFiles: "image/*,.pdf,.doc,.docx",
                addRemoveLinks: true,
                init: function() {
                    var myDropzone = this;
                    
                    // Handle form submission
                    $("form").on("submit", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        if (myDropzone.getQueuedFiles().length > 0) {
                            myDropzone.processQueue();
                        } else {
                            // If no files to upload, just submit the form
                            this.submit();
                        }
                    });

                    // Handle the upload completion
                    this.on("sendingmultiple", function(files, xhr, formData) {
                        // Append form data
                        var form = document.querySelector("form");
                        var formData = new FormData(form);
                        for (var pair of formData.entries()) {
                            xhr.append(pair[0], pair[1]);
                        }
                    });

                    this.on("successmultiple", function(files, response) {
                        window.location.href = "{{ route('projects.seo-logs.index', $project) }}";
                    });

                    this.on("errormultiple", function(files, response) {
                        console.error(response);
                    });
                }
            });
        });
    </script>
    @endpush
</x-app-layout> 