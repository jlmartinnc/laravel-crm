<v-inline-select-edit
    {{ $attributes->except('options') }}
    :options="{{ json_encode($options) }}"
>
    <div class="group w-full max-w-full hover:rounded-sm">
        <div class="rounded-xs flex h-[34px] items-center space-x-2 pl-2.5 text-left">
            <div class="shimmer h-5 w-48 rounded border border-transparent"></div>
        </div>
    </div>
</v-inline-select-edit>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-inline-select-edit-template"
    >
        <div class="group w-full max-w-full hover:rounded-sm">
            <!-- Non-editing view -->
            <div
                v-if="! isEditing"
                class="rounded-xs flex h-[34px] items-center"
                :class="allowEdit ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800' : ''"
                :style="textPositionStyle"
            >
                <x-admin::form.control-group.control
                    type="hidden"
                    ::id="name"
                    ::name="name"
                    v-model="inputValue"
                />

                <div class="group relative flex !w-full flex-col items-center">
                    <span class="w-40 truncate rounded border border-transparent pl-[2px]">@{{ selectedValue?.name }}</span>

                    <div
                        class="absolute bottom-0 mb-5 hidden flex-col items-center group-hover:flex"
                        v-if="selectedValue?.name > 20"
                    >
                        <span class="whitespace-no-wrap relative z-10 rounded-md bg-black p-4 text-xs leading-none text-white shadow-lg dark:bg-white dark:text-gray-900">
                            @{{ selectedValue?.name }}
                        </span>

                        <div class="-mt-2 h-3 w-3 rotate-45 bg-black dark:bg-white"></div>
                    </div>
                </div>

                <template v-if="allowEdit">
                    <i
                        @click="toggle"
                        class="icon-edit hidden pr-2 text-xl group-hover:block"
                    ></i>
                </template>
            </div>
        
            <!-- Editing view -->
            <div
                class="relative flex w-full flex-col"
                v-else
            >
                <div class="relative flex w-full flex-col">
                    <x-admin::form.control-group.control
                        type="select"
                        ::id="name"
                        ::name="name"
                        class="!h-[34px] !py-0"
                        ::rules="rules"
                        ::label="label"
                        ::placeholder="placeholder"
                        ::style="inputPositionStyle"
                        v-model="inputValue"
                    >
                        <option
                            v-for="(option, index) in options"
                            :key="option.id"
                            :value="option.id"
                        >
                            @{{ option.name }}
                        </option>
                    </x-admin::form.control-group.control>
                        
                    <!-- Action Buttons -->
                    <div class="absolute right-2 top-1/2 flex -translate-y-1/2 transform items-center gap-[1px] bg-white">
                        <i class="icon-down-arrow text-2xl" />

                        <button
                            type="button"
                            class="flex items-center justify-center rounded-l-md bg-green-100 p-1 hover:bg-green-200"
                            @click="save"
                        >
                            <i class="icon-tick text-md cursor-pointer font-bold text-green-600" />
                        </button>
                    
                        <button
                            type="button"
                            class="ml-[1px] flex items-center justify-center rounded-r-md bg-red-100 p-1 hover:bg-red-200"
                            @click="cancel"
                        >
                            <i class="icon-cross-large text-md cursor-pointer font-bold text-red-600" />
                        </button>
                    </div>
                </div>

                <x-admin::form.control-group.error ::name="name"/>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-inline-select-edit', {
            template: '#v-inline-select-edit-template',

            emits: ['on-change', 'on-cancelled'],

            props: {
                name: {
                    type: String,
                    required: true,
                },

                value: {
                    required: true,
                },

                rules: {
                    type: String,
                    default: '',
                },

                label: {
                    type: String,
                    default: '',
                },

                placeholder: {
                    type: String,
                    default: '',
                },

                position: {
                    type: String,
                    default: 'right',
                },

                allowEdit: {
                    type: Boolean,
                    default: true,
                },

                errors: {
                    type: Object,
                    default: {},
                },

                options: {
                    type: Array,
                    required: true,
                },

                url: {
                    type: String,
                    default: '',
                },
            },

            data() {
                return {
                    inputValue: this.value,

                    isEditing: false,
                };
            },

            watch: {
                /**
                 * Watch the value prop.
                 * 
                 * @param {String} newValue 
                 */
                value(newValue) {
                    this.inputValue = newValue;
                },
            },

            computed: {
                /**
                 * Get the input position style.
                 * 
                 * @return {String}
                 */
                inputPositionStyle() {
                    return this.position === 'left' ? 'text-align: left; padding-left: 9px' : 'text-align: right;';
                },

                /**
                 * Get the text position style.
                 * 
                 * @return {String}
                 */
                textPositionStyle() {
                    return this.position === 'left' ? 'justify-content: space-between' : 'justify-content: end';
                },

                /**
                 * Get the selected value.
                 * 
                 * @return {Object}
                 */
                selectedValue() {
                    return this.options.find((option, index) => option.id == this.inputValue);
                },
            },

            methods: {
                /**
                 * Toggle the input.
                 * 
                 * @return {void}
                 */
                toggle() {
                    this.isEditing = true;
                },

                /**
                 * Save the input value.
                 * 
                 * @return {void}
                 */
                save() {
                    if (this.errors[this.name]) {
                        return;
                    }

                    this.isEditing = false;

                    if (this.url) {
                        this.$axios.put(this.url, {
                                [this.name]: this.inputValue,
                            })
                            .then((response) => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            })
                            .catch((error) => {
                                this.inputValue = this.value;
                            });                        
                    }

                    this.$emit('on-change', {
                        name: this.name,
                        value: this.inputValue,
                    });
                },

                /**
                 * Cancel the input value.
                 * 
                 * @return {void}
                 */
                cancel() {
                    this.inputValue = this.value;

                    this.isEditing = false;

                    this.$emit('on-cancelled', {
                        name: this.name,
                        value: this.inputValue,
                    });
                },
            },
        });
    </script>
@endPushOnce