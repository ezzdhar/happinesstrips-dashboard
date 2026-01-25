@props([
    'wireModel' => null,
    'label' => null,
    'hint' => null,
    'accept' => 'image/*',
    'maxFiles' => 10,
    'maxFileSize' => 5, // MB
    'preview' => [],
    'deleteAction' => null,
])

@php
    // Support both wire:model and wire-model
    $wireModelName = $wireModel ?? $attributes->wire('model')->value();
    $id = 'dropzone-' . uniqid();
@endphp

@assets
    <style>
        .custom-dropzone-area {
            border: 2px dashed #d1d5db;
            border-radius: 0.75rem;
            background: transparent;
            transition: all 0.3s ease;
            padding: 2rem;
        }

        .custom-dropzone-area:hover,
        .custom-dropzone-area.dz-drag-hover {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.05);
        }

        .dark .custom-dropzone-area {
            border-color: #4b5563;
        }

        .dark .custom-dropzone-area:hover {
            border-color: #818cf8;
        }

        .file-item-progress {
            height: 6px;
            border-radius: 3px;
            background: #e5e7eb;
            overflow: hidden;
        }

        .file-item-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .file-item-progress-bar.uploading {
            background: linear-gradient(90deg, #3b82f6, #6366f1);
        }

        .file-item-progress-bar.error {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }

        .dark .file-item-progress {
            background: #374151;
        }
    </style>
@endassets

<div class="w-full">
    @if ($label)
        <label class="label">
            <span class="label-text font-semibold">{{ $label }}</span>
        </label>
    @endif

    {{-- Alpine-managed section - completely isolated from Livewire --}}
    <div x-data="dropzoneListHandler(@js($wireModelName), @js($maxFiles), @js($maxFileSize), @js($accept))" x-init="init()" wire:ignore>
        {{-- Dropzone Area --}}
        <div class="custom-dropzone-area cursor-pointer" @click="openFilePicker()" @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop($event)"
            :class="{ 'dz-drag-hover': isDragging }">
            <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" accept="{{ $accept }}"
                multiple class="hidden">

            <div class="text-center">
                <p class="text-gray-500 dark:text-gray-400 mb-3">
                    {{ __('lang.max_file_size') }}: {{ $maxFileSize }}MB
                </p>

                <button type="button" class="btn btn-outline btn-primary btn-sm gap-2" @click.stop="openFilePicker()">
                    <x-icon name="o-cloud-arrow-up" class="w-4 h-4" />
                    {{ __('lang.drag_drop_images') }}
                </button>
            </div>
        </div>

        @if ($hint)
            <p class="mt-2 text-sm text-gray-500">{{ $hint }}</p>
        @endif

        {{-- Upload Status --}}
        <div x-show="isUploading" x-cloak class="mt-3">
            <div class="flex items-center gap-2">
                <span class="loading loading-spinner loading-sm text-primary"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('lang.uploading') }}... (<span x-text="uploadedCount"></span>/<span
                        x-text="totalFiles"></span>)
                </span>
            </div>
        </div>

        {{-- Files List --}}
        <div x-ref="filesList" class="mt-4 space-y-3"></div>

        {{-- Error Messages --}}
        <template x-for="(error, idx) in errors" :key="idx">
            <div class="alert alert-error text-sm py-2 mt-2">
                <x-icon name="o-exclamation-circle" class="w-4 h-4" />
                <span x-text="error"></span>
                <button type="button" @click="errors.splice(idx, 1)" class="btn btn-ghost btn-xs">✕</button>
            </div>
        </template>
    </div>

    {{-- Existing Images Preview (Livewire-managed) --}}
    @if (!empty($preview))
        <div class="mt-4">
            <h4 class="text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">{{ __('lang.existing_images') }}
            </h4>
            <div class="space-y-2">
                @foreach ($preview as $image)
                    <div
                        class="bg-base-100 dark:bg-base-200 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 flex-shrink-0 rounded-lg overflow-hidden bg-base-200">
                                <img src="{{ \App\Services\FileService::get($image['path'] ?? $image) }}" alt=""
                                    class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700 dark:text-gray-300 truncate">
                                    {{ basename($image['path'] ?? $image) }}
                                </p>
                            </div>
                            @if ($deleteAction)
                                <button type="button"
                                    wire:click="{{ $deleteAction }}({{ $image['id'] ?? $loop->index }})"
                                    wire:loading.attr="disabled"
                                    wire:confirm="{{ __('lang.confirm_delete', ['attribute' => __('lang.image')]) }}"
                                    class="text-gray-400 hover:text-red-500 transition-colors p-1">
                                    <x-icon name="o-trash" class="w-4 h-4" />
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@script
    <script>
        Alpine.data('dropzoneListHandler', (wireModel, maxFiles, maxFileSizeMB, acceptedFiles) => ({
            files: [],
            errors: [],
            isDragging: false,
            isUploading: false,
            uploadedCount: 0,
            totalFiles: 0,
            fileIdCounter: 0,
            maxFileSize: maxFileSizeMB * 1024 * 1024,
            uploadQueue: [],
            isProcessingQueue: false,

            init() {
                console.log('[Dropzone] Initialized with wireModel:', wireModel);
            },

            openFilePicker() {
                this.$refs.fileInput.click();
            },

            handleDrop(event) {
                this.isDragging = false;
                console.log('[Dropzone] Files dropped:', event.dataTransfer.files.length);
                this.processFiles(event.dataTransfer.files);
            },

            handleFileSelect(event) {
                console.log('[Dropzone] Files selected:', event.target.files.length);
                this.processFiles(event.target.files);
                event.target.value = '';
            },

            processFiles(fileList) {
                console.log('[Dropzone] Processing files:', fileList.length);

                for (let i = 0; i < fileList.length; i++) {
                    const file = fileList[i];
                    console.log('[Dropzone] Processing:', file.name, this.formatSize(file.size));

                    // Check max files
                    if (this.files.length >= maxFiles) {
                        this.errors.push(`{{ __('lang.max_files_reached') }} (${maxFiles})`);
                        break;
                    }

                    // Validate file type
                    if (!this.isValidType(file)) {
                        this.errors.push(`${file.name}: {{ __('lang.invalid_file_type') }}`);
                        continue;
                    }

                    // Validate file size
                    if (file.size > this.maxFileSize) {
                        this.errors.push(
                            `${file.name}: {{ __('lang.file_too_large') }} (max ${maxFileSizeMB}MB)`);
                        continue;
                    }

                    // Create file entry
                    const fileId = ++this.fileIdCounter;
                    const fileEntry = {
                        id: fileId,
                        file: file,
                        name: file.name,
                        size: file.size,
                        progress: 0,
                        status: 'pending'
                    };

                    this.files.push(fileEntry);
                    this.totalFiles = this.files.length;
                    this.uploadQueue.push(fileEntry);

                    // Render file item immediately
                    this.renderFileItem(fileEntry);

                    // Create preview
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.updateFilePreview(fileId, e.target.result);
                        };
                        reader.readAsDataURL(file);
                    }
                }

                // Start queue processing
                this.processQueue();
            },

            renderFileItem(fileEntry) {
                const container = this.$refs.filesList;
                const div = document.createElement('div');
                div.id = `file-item-${fileEntry.id}`;
                div.className =
                    'bg-base-100 dark:bg-base-200 rounded-lg p-3 border border-gray-200 dark:border-gray-700';
                div.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 flex-shrink-0 rounded-lg overflow-hidden bg-base-200 dark:bg-base-300">
                    <div id="preview-${fileEntry.id}" class="w-full h-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">${fileEntry.name}</p>
                        <button type="button" onclick="document.dispatchEvent(new CustomEvent('dropzone-remove', {detail: ${fileEntry.id}}))" class="text-gray-400 hover:text-red-500 transition-colors p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mb-2">${this.formatSize(fileEntry.size)}</p>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 file-item-progress">
                            <div id="progress-bar-${fileEntry.id}" class="file-item-progress-bar" style="width: 0%"></div>
                        </div>
                        <span id="progress-text-${fileEntry.id}" class="text-xs font-medium w-10 text-right text-gray-600">0%</span>
                    </div>
                </div>
            </div>
        `;
                container.appendChild(div);

                // Listen for remove event
                document.addEventListener('dropzone-remove', (e) => {
                    if (e.detail === fileEntry.id) {
                        this.removeFile(fileEntry.id);
                    }
                }, {
                    once: true
                });
            },

            updateFilePreview(fileId, src) {
                const previewEl = document.getElementById(`preview-${fileId}`);
                if (previewEl) {
                    previewEl.innerHTML = `<img src="${src}" class="w-full h-full object-cover" />`;
                }
            },

            updateFileProgress(fileId, progress, status) {
                const progressBar = document.getElementById(`progress-bar-${fileId}`);
                const progressText = document.getElementById(`progress-text-${fileId}`);

                if (progressBar) {
                    progressBar.style.width = `${progress}%`;
                    progressBar.classList.remove('uploading', 'error');
                    if (status === 'uploading') progressBar.classList.add('uploading');
                    if (status === 'error') progressBar.classList.add('error');
                }

                if (progressText) {
                    progressText.textContent = status === 'error' ? 'Error' : `${progress}%`;
                    progressText.className = 'text-xs font-medium w-10 text-right';
                    if (status === 'done') progressText.classList.add('text-green-600');
                    else if (status === 'uploading') progressText.classList.add('text-blue-600');
                    else if (status === 'error') progressText.classList.add('text-red-600');
                    else progressText.classList.add('text-gray-600');
                }
            },

            isValidType(file) {
                const accepted = acceptedFiles.split(',').map(t => t.trim());
                for (let type of accepted) {
                    if (type === 'image/*' && file.type.startsWith('image/')) return true;
                    if (type === file.type) return true;
                    if (type.startsWith('.') && file.name.toLowerCase().endsWith(type.toLowerCase()))
                        return true;
                }
                return false;
            },

            async processQueue() {
                if (this.isProcessingQueue) return;

                this.isProcessingQueue = true;
                this.isUploading = true;
                console.log('[Dropzone] Processing queue:', this.uploadQueue.length, 'files');

                while (this.uploadQueue.length > 0) {
                    const fileEntry = this.uploadQueue.shift();
                    console.log('[Dropzone] Uploading:', fileEntry.name);

                    try {
                        await this.uploadSingleFile(fileEntry);
                    } catch (error) {
                        console.error('[Dropzone] Upload failed:', fileEntry.name, error);
                    }
                }

                this.isProcessingQueue = false;
                this.isUploading = false;
                console.log('[Dropzone] Queue complete');
            },

            uploadSingleFile(fileEntry) {
                return new Promise((resolve, reject) => {
                    fileEntry.status = 'uploading';
                    this.updateFileProgress(fileEntry.id, 0, 'uploading');

                    this.$wire.upload(
                        wireModel,
                        fileEntry.file,
                        // Success
                        (uploadedFilename) => {
                            console.log('[Dropzone] Success:', fileEntry.name);
                            fileEntry.progress = 100;
                            fileEntry.status = 'done';
                            this.updateFileProgress(fileEntry.id, 100, 'done');
                            this.uploadedCount++;
                            resolve();
                        },
                        // Error
                        (error) => {
                            console.error('[Dropzone] Error:', fileEntry.name, error);
                            fileEntry.status = 'error';
                            this.updateFileProgress(fileEntry.id, 0, 'error');
                            this.errors.push(`${fileEntry.name}: Upload failed`);
                            resolve(); // Don't reject, continue with next
                        },
                        // Progress
                        (event) => {
                            // Livewire passes ProgressEvent, get percentage from it
                            let progress = 0;
                            if (typeof event === 'number') {
                                progress = event;
                            } else if (event && event.lengthComputable) {
                                progress = Math.round((event.loaded / event.total) * 100);
                            } else if (event && event.detail && event.detail.progress) {
                                progress = event.detail.progress;
                            }
                            console.log('[Dropzone] Progress:', fileEntry.name, progress + '%');
                            fileEntry.progress = progress;
                            this.updateFileProgress(fileEntry.id, progress, 'uploading');
                        },
                        // Cancel
                        () => {}
                    );
                });
            },

            removeFile(fileId) {
                console.log('[Dropzone] Removing file:', fileId);

                // Remove from files array
                const idx = this.files.findIndex(f => f.id === fileId);
                if (idx > -1) {
                    this.files.splice(idx, 1);
                    this.totalFiles = this.files.length;
                }

                // Remove from queue
                const queueIdx = this.uploadQueue.findIndex(f => f.id === fileId);
                if (queueIdx > -1) {
                    this.uploadQueue.splice(queueIdx, 1);
                }

                // Remove DOM element
                const el = document.getElementById(`file-item-${fileId}`);
                if (el) el.remove();

                // Update Livewire model
                try {
                    const currentFiles = this.$wire.get(wireModel) || [];
                    if (currentFiles.length > idx && idx >= 0) {
                        currentFiles.splice(idx, 1);
                        this.$wire.set(wireModel, currentFiles);
                    }
                } catch (e) {
                    console.error('[Dropzone] Error updating model:', e);
                }
            },

            formatSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        }));
    </script>
@endscript
