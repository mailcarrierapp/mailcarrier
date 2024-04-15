<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />

        <meta name="application-name" content="{{ config('app.name') }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf_token" value="{{ csrf_token() }}"/>

        <title>MailCarrier preview</title>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @livewireStyles
    </head>

    <body class="antialiased">
        {{ $slot }}

        @livewireScripts
    </body>
</html>
