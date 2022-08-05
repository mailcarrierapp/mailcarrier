@php
    Filament\Facades\Filament::registerRenderHook(
        'styles.end',
        fn () => '<link href="' . mix('css/app.css') . '" rel="stylesheet" />',
    );
@endphp

<x-filament::layouts.card title="Unauthorized">
    <p class="mb-6">Your account is not authorized to perform this action.</p>

    <a href="/">
        <x-filament::button class="w-full">
            Go back
        </x-filament::button>
    </a>
</x-filament::layouts.card>
