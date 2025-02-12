<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add SEO Log for') }} {{ $project->name }}
        </h2>
    </x-slot>

    <!-- Include Quill stylesheet -->
    @push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .ql-editor {
            min-height: 300px;
            font-size: 16px;
            line-height: 1.6;
            padding: 20px;
        }
        .ql-toolbar.ql-snow {
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
            border-color: #d1d5db;
            padding: 12px;
        }
        .ql-container.ql-snow {
            border-bottom-left-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
            border-color: #d1d5db;
        }
        .ql-editor.ql-blank::before {
            font-style: normal;
            color: #9ca3af;
        }
        .file-drop-area {
            border: 2px dashed #cbd5e0;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            background-color: #f9fafb;
        }
        .file-drop-area.dragover {
            border-color: #4f46e5;
            background-color: #eef2ff;
        }
        .attachment-preview {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            margin: 0.5rem 0;
            background-color: #f3f4f6;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
        }
        .attachment-preview .remove-attachment {
            margin-left: auto;
            color: #ef4444;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: all 0.2s;
        }
        .attachment-preview .remove-attachment:hover {
            background-color: #fee2e2;
        }
    </style>
    @endpush

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form id="seo-log-form" action="{{ route('projects.seo-logs.store', $project) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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
                            <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="analysis">Analysis</option>
                                <option value="optimization">Optimization</option>
                                <option value="report">Report</option>
                                <option value="other">Other</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Content -->
                        <div>
                            <x-input-label for="content" :value="__('Content')" />
                            <div id="editor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                            <textarea id="content" name="content" class="hidden">{{ old('content') }}</textarea>
                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                        </div>

                        <!-- File Attachments -->
                        <div>
                            <x-input-label :value="__('Attachments')" />
                            <div id="file-drop-area" class="file-drop-area mt-1">
                                <div class="file-upload-message">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <p class="mt-1 text-sm text-gray-600">Drag and drop files here or click to select files</p>
                                    <p class="mt-1 text-xs text-gray-500">Supported formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF</p>
                                </div>
                                <input type="file" id="file-input" name="attachments[]" multiple class="hidden">
                            </div>
                            <div id="file-preview" class="mt-4 space-y-2"></div>
                            <x-input-error :messages="$errors->get('attachments')" class="mt-2" />
                            <x-input-error :messages="$errors->get('attachments.*')" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-4">
                            <x-secondary-button type="button" onclick="window.history.back()">
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
    <!-- Include Quill -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Quill editor
            var quill = new Quill('#editor', {
                theme: 'snow',
                placeholder: 'Enter your content here...',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'script': 'sub'}, { 'script': 'super' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });

            // Set initial content if it exists
            const content = document.getElementById('content').value;
            if (content) {
                quill.root.innerHTML = content;
            }

            // File Upload Handling
            const dropArea = document.getElementById('file-drop-area');
            const fileInput = document.getElementById('file-input');
            const filePreview = document.getElementById('file-preview');
            const maxFileSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            // Highlight drop zone when dragging over it
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });

            // Handle dropped files
            dropArea.addEventListener('drop', handleDrop, false);
            
            // Handle clicked files
            dropArea.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', handleFiles);

            function preventDefaults (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function highlight(e) {
                dropArea.classList.add('dragover');
            }

            function unhighlight(e) {
                dropArea.classList.remove('dragover');
            }

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles({ target: { files: files }});
            }

            function handleFiles(e) {
                const files = [...e.target.files];
                files.forEach(previewFile);
            }

            function previewFile(file) {
                // Validate file type
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Please upload only supported file formats.');
                    return;
                }

                // Validate file size
                if (file.size > maxFileSize) {
                    alert('File is too large. Maximum size is 5MB.');
                    return;
                }

                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function() {
                    const preview = document.createElement('div');
                    preview.className = 'attachment-preview';
                    
                    // Create file icon/preview based on type
                    let filePreviewHtml = '';
                    if (file.type.startsWith('image/')) {
                        filePreviewHtml = `<img src="${reader.result}" class="h-10 w-10 object-cover rounded">`;
                    } else {
                        filePreviewHtml = `<svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>`;
                    }

                    preview.innerHTML = `
                        ${filePreviewHtml}
                        <span class="ml-3 text-sm text-gray-700">${file.name}</span>
                        <button type="button" class="remove-attachment ml-auto text-red-500 hover:text-red-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    `;

                    preview.querySelector('.remove-attachment').addEventListener('click', function() {
                        preview.remove();
                    });

                    filePreview.appendChild(preview);
                }
            }
        });
    </script>
    @endpush
</x-app-layout> 