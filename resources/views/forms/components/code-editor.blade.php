{{-- Adapted from https://github.com/dotswan/filament-code-editor --}}
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="relative max-w-full overflow-hidden rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500 h-[365px]">
        <div
            class="w-full h-full"
            x-data="codeEditorFormComponent({
                state: $wire.$entangle('{{ $getStatePath() }}'),
                isReadOnly: @js($isDisabled()),
            })">
            <div
                wire:ignore
                x-ref="codeEditor"
                class="w-full h-full">
            </div>
        </div>
    </div>
</x-dynamic-component>
