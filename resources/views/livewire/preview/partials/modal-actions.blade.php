<template x-if="withEditor">
    <div class="flex gap-4">
        <x-filament::button color="gray" x-on:click="$dispatch('open-modal', { id: 'preview-on-device' })">
            Preview on device
        </x-filament::button>

        <x-filament::button x-on:click="acceptEditorChanges()">
            Accept & close
        </x-filament::button>
    </div>
</template>

<template x-if="!withEditor">
    <x-filament::button color="gray" x-on:click="closePreviewModal()">
        {{ __('filament-peek::ui.close-modal-action-label') }}
    </x-filament::button>
</template>

<x-filament::modal
    id="preview-on-device"
    x-on:modal-opened="
        document.getElementById('device-preview-qrcode').setAttribute(
            'contents',
            document.querySelector('.filament-peek-preview iframe').getAttribute('src')
        );
    ">
    <x-slot name="heading">
        Preview on device
    </x-slot>

    Scan this QRCode from your device to preview the current template:

    <qr-code
        id="device-preview-qrcode"
        contents="https://mailcarrier.app"
        module-color="#1c7d43"
        position-ring-color="#13532d"
        position-center-color="#70c559"
        style="
            width: 300px;
            height: 300px;
            margin: 2em auto;
            background-color: #fff;
        "></qr-code>

    <x-slot name="footerActions">
    </x-slot>
</x-filament::modal>
