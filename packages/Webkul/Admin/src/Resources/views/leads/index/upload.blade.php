<v-upload>
    <button
        type="button"
        class="secondary-button"
    >
        @lang('admin::app.leads.index.upload.upload-pdf')
    </button>
</v-upload>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="upload-template"
    >
        <div>
            <button
                type="button"
                class="secondary-button"
                @click="$refs.userUpdateAndCreateModal.open()"
            >
                @lang('admin::app.leads.index.upload.upload-pdf')
            </button>

            <x-admin::form
                v-slot="{ meta, values, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form 
                    @submit="handleSubmit($event, create)"
                    enctype="multipart/form-data"
                    ref="userForm"
                >
                    <x-admin::modal ref="userUpdateAndCreateModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @lang('admin::app.leads.index.upload.create-lead')
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.leads.index.upload.file')
                                </x-admin::form.control-group.label>

                                <v-field
                                    v-slot="{ field, errors }"
                                    id="files"
                                    name="files"
                                    class="mb-4"
                                    label="@lang('admin::app.leads.index.upload.file')"
                                    rules="required|pdf|mimes:pdf"
                                    @change="handleFileUpload"
                                >
                                    <input
                                        type="file"
                                        v-bind="{ name: field.name }"
                                        id="files"
                                        name="files"
                                        :class="[errors.length ? 'border !border-red-600 hover:border-red-600' : '']"
                                        class="w-full rounded border border-gray-200 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:file:bg-gray-800 dark:file:dark:text-white dark:hover:border-gray-400 dark:focus:border-gray-400"
                                        accept=".pdf"
                                        ::disabled="isLoading"
                                        multiple
                                    />
                                </v-field>

                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.leads.index.upload.file-info')
                                </p>

                                <x-admin::form.control-group.error control-name="files" />
                            </x-admin::form.control-group>

                            <!-- Sample Downloadable file -->
                            <a
                                href="{{ Storage::url('/lead-samples/sample.pdf') }}"
                                target="_blank"
                                id="source-sample"
                                class="cursor-pointer text-sm text-blue-600 transition-all hover:underline"
                                download
                            >
                                @lang('admin::app.leads.index.upload.sample-pdf')
                            </a>
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <x-admin::button
                                button-type="submit"
                                class="primary-button justify-center"
                                :title="trans('admin::app.leads.index.upload.save-btn')"
                                ::loading="isLoading"
                                ::disabled="isLoading"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </div>
    </script>

    <script type="module">
        app.component('v-upload', {
            template: '#upload-template',

            data() {
                return {
                    isLoading: false,

                    selectedFiles: [],
                };
            },

            methods: {
                handleFileUpload(event) {
                    this.selectedFiles = Array.from(event.target.files);
                },

                create(params, { resetForm, setErrors }) {
                    if (this.selectedFiles.length === 0) {
                        this.$emitter.emit('add-flash', { type: 'error', message: "Please select at least one file." });
                        return;
                    }

                    this.isLoading = true;

                    const formData = new FormData();

                    this.selectedFiles.forEach((file, index) => {
                        formData.append(`files[${index}]`, file);
                    });

                    formData.append('_method', 'post');

                    this.sendRequest(formData);
                },

                sendRequest(formData) {
                    this.$axios.post("{{ route('admin.leads.create_by_ai') }}", formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        }
                    })
                    .then(response => {
                        this.isLoading = false;

                        this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                        this.$refs.userUpdateAndCreateModal.close();

                        this.$parent.$refs.leadsKanban.boot()
                    })
                    .catch(error => {
                        this.isLoading = false;

                        this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });

                        this.$refs.userUpdateAndCreateModal.close();
                    });
                },
            },
        });
    </script>
@endPushOnce
