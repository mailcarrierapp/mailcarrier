<template x-if="withEditor">
    <div class="flex gap-4">
        <x-filament::button
            class="!bg-amber-500 hover:!bg-amber-400"
            x-on:click="$dispatch('open-modal', { id: 'preview-on-device' })">
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
    width="lg"
    x-on:modal-opened="
        document.getElementById('device-preview-qrcode').setAttribute(
            'contents',
            document.querySelector('.filament-peek-preview iframe').getAttribute('src')
        );
    ">
    <x-slot name="heading">
        Preview on device
    </x-slot>

    @if (\MailCarrier\Facades\MailCarrier::isLocalhost())
        <div class="bg-warning-100 dark:bg-warning-500/20 border border-warning-300 dark:border-warning-600 rounded py-2 px-4 dark:text-warning-300 font-semibold">
            Device preview will not work on localhost without a tunneling service like ngrok or expose.
        </div>
    @endif

    Scan this QRCode from your device to live-preview the current template.

    <qr-code
        id="device-preview-qrcode"
        contents="https://mailcarrier.app"
        module-color="#1c7d43"
        position-ring-color="#13532d"
        position-center-color="#70c559"
        style="
            width: 300px;
            height: 300px;
            margin: auto;
            background-color: #fff;
        "></qr-code>

    <x-slot name="footerActions">
        <x-filament::button color="gray" class="mx-auto" x-on:click="close()">
            Close
        </x-filament::button>
    </x-slot>
</x-filament::modal>
