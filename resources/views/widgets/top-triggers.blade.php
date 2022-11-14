<x-filament::widget class="filament-widgets-top-triggers-widget">
    <x-filament::card>
        <div class="flex items-center justify-between gap-8 py-1">
            <x-filament::card.heading>
                {{ $heading }}
            </x-filament::card.heading>
        </div>

        <x-filament::hr />

        @if ($data->isEmpty())
            <p class="italic text-center">No data available</p>
        @else
            <x-tables::table>
                <x-slot name="header">
                    <x-tables::header-cell>
                        Trigger
                    </x-tables::header-cell>
                    <x-tables::header-cell alignment="center">
                        Count
                    </x-tables::header-cell>
                </x-slot>

                @foreach ($data as $row)
                    <x-tables::row>
                        <x-tables::cell class="px-4 py-1">
                            {{ $row->trigger }}
                        </x-tables::cell>
                        <x-tables::cell class="px-4 py-1 text-center">
                            {{ $row->count }}
                        </x-tables::cell>
                    </x-tables::row>
                @endforeach
            </x-tablex::table>
        @endif
    </x-tables::card>
</x-filament::widget>
