<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New article') }}
        </h2>
    </x-slot>
    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <x-jet-form-section submit="save">
                <x-slot name="title">
                    {{ __('Article') }}
                </x-slot>
                <x-slot name="description">
                    {{ __('Create a new article') }}
                </x-slot>
                <x-slot name="form">
                    <div class="col-span-6 sm:col-span-4">
                        <x-select-image wire:model="image" :image="$image" :existing="$article->image"></x-select-image>
                        <x-jet-input-error for="image" class="mt-2"></x-jet-input-error>
                    </div>

                    <div class="col-span-6 sm:col-span-4">
                        <x-jet-label for="title" :value="__('Title')"></x-jet-label>
                        <x-jet-input id="title" wire:model="article.title" class="mt-1 block w-full"
                                     type="text"></x-jet-input>
                        <x-jet-input-error for="article.title" class="mt-2"></x-jet-input-error>
                    </div>

                    <div class="col-span-6 sm:col-span-4">
                        <x-jet-label for="slug" :value="__('Slug')"></x-jet-label>
                        <x-jet-input id="slug" wire:model="article.slug" class="mt-1 block w-full"
                                     type="text"></x-jet-input>
                        <x-jet-input-error for="article.slug" class="mt-2"></x-jet-input-error>
                    </div>

                    <div class="col-span-6 sm:col-span-4">
                        <x-jet-label for="category_id" :value="__('Category')"></x-jet-label>
                        <div class="flex m-1 space-x-2">
                            <x-select id="category_id"
                                      wire:model="article.category_id"
                                      :options="$categories"
                                      :placeholder="__('Select category')"
                                      class="block w-full"></x-select>
                            <x-jet-secondary-button
                                wire:click="openCategoryForm"
                            >+
                            </x-jet-secondary-button>
                        </div>
                        <x-jet-input-error for="article.category_id" class="mt-2"></x-jet-input-error>
                    </div>

                    <div class="col-span-6 sm:col-span-4">
                        <x-jet-label for="content" :value="__('Slug')"></x-jet-label>
                        <x-html-editor id="content" wire:model="article.content"
                                       class="mt-1 block w-full"></x-html-editor>
                        <x-jet-input-error for="article.content" class="mt-2"></x-jet-input-error>
                    </div>
                </x-slot>
                <x-slot name="actions">
                    <x-jet-button class="mr-4" wire:click="save">
                        {{ __('Save') }}
                    </x-jet-button>
                </x-slot>
            </x-jet-form-section>
        </div>
    </div>

    <x-jet-modal wire:model="showCategoryModal">
        <form wire:submit.prevent="saveNewCategory">
            <div class="px-6 py-4">
                <div class="text-lg">
                    {{ __('Create category') }}
                </div>

                <div class="mt-4 space-y-3">
                    <div class="col-span-6 sm:col-span-4">
                        <x-jet-label for="new-category_name" :value="__('Name')"></x-jet-label>
                        <x-jet-input wire:model="newCategory.name" id="new-category_name" class="mt-1 block w-full"
                                     type="text"></x-jet-input>
                        <x-jet-input-error for="newCategory.name" class="mt-2"></x-jet-input-error>
                    </div>
                    <div class="col-span-6 sm:col-span-4">
                        <x-jet-label for="new-category_slug" :value="__('Slug')"></x-jet-label>
                        <x-jet-input wire:model="newCategory.slug" id="new-category_slug" class="mt-1 block w-full"
                                     type="text"></x-jet-input>
                        <x-jet-input-error for="newCategory.slug" class="mt-2"></x-jet-input-error>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-100 text-right">
                <x-jet-button>
                    {{ __('Create') }}
                </x-jet-button>
                <x-jet-secondary-button wire:click="closeCategoryForm">
                    {{ __('Cancel') }}
                </x-jet-secondary-button>
            </div>
        </form>
    </x-jet-modal>
</div>
