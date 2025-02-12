<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add SEO Log for') }} {{ $project->name }}
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
    </style>
    @endpush

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('projects.seo-logs.store', $project) }}" class="space-y-6" enctype="multipart/form-data">
                        @csrf

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Type -->
                        <div>
                            <x-input-label for="type" :value="__('Type')" />
                            <select id="type" name="type" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="seo_analytics_reporting" {{ old('type') == 'seo_analytics_reporting' ? 'selected' : '' }}>SEO Analytics & Reporting</option>
                                <option value="technical_seo" {{ old('type') == 'technical_seo' ? 'selected' : '' }}>Technical SEO</option>
                                <option value="on_page_seo" {{ old('type') == 'on_page_seo' ? 'selected' : '' }}>On-Page SEO</option>
                                <option value="off_page_seo" {{ old('type') == 'off_page_seo' ? 'selected' : '' }}>Off-Page SEO</option>
                                <option value="local_seo" {{ old('type') == 'local_seo' ? 'selected' : '' }}>Local SEO</option>
                                <option value="content_seo" {{ old('type') == 'content_seo' ? 'selected' : '' }}>Content SEO</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Content -->
                        <div>
                            <x-input-label for="content" :value="__('Content')" />
                            <textarea id="content" name="content" class="summernote">{{ old('content') }}</textarea>
                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                        </div>

                        <!-- File Attachments -->
                        <div>
                            <x-input-label :value="__('Attachments')" class="mb-2" />
                            <div id="dropzone" class="dropzone"></div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-secondary-button onclick="window.history.back()" type="button" class="mr-3">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button>
                                {{ __('Create Log') }}
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
                url: "{{ route('projects.seo-logs.store', $project) }}",
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