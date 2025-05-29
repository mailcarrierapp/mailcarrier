<div
    class="text-sm"
    x-data="{
        activeTab: null,
        highlighted: false,

        init() {
            this.setTab('data');

            if (localStorage.getItem('theme') === 'light') {
                document.getElementById('log-variables').classList.add('theme-light');
            } else {
                document.getElementById('log-variables').classList.add('theme-dark');
            }
        },
        setTab(tabName) {
            this.activeTab = tabName;

            if (tabName === 'data' && !this.highlighted) {
                this.highlighted = true;
                hljs.highlightAll();
            }
        },
    }"
    x-init="init()">
    <x-filament::tabs class="mb-6" style="width: fit-content">
        <x-filament::tabs.item
            icon="heroicon-o-at-symbol"
            alpine-active="activeTab === 'data'"
            x-on:click="setTab('data')">
            Data
        </x-filament::tabs.item>

        <x-filament::tabs.item
            icon="heroicon-o-paper-clip"
            alpine-active="activeTab === 'attachments'"
            x-on:click="setTab('attachments')">
            Attachments ({{ count($log->attachments) }})
        </x-filament::tabs.item>

        <x-filament::tabs.item
            icon="heroicon-o-rss"
            alpine-active="activeTab === 'events'"
            x-on:click="setTab('events')">
            Events ({{ count($log->events) }})
        </x-filament::tabs.item>
    </x-filament::tabs>

    <!-- Data -->
    <x-filament::section x-show="activeTab === 'data'">
        <div>
            <p class="font-bold mb-1">Sender</p>
            @if ($log->sender->name)
                <p>{{ $log->sender->name }} &lt;{{ $log->sender->email }}&gt;</p>
            @else
                <p>{{ $log->sender->email }}</p>
            @endif
        </div>

        <div class="mt-4">
            <p class="font-bold mb-1">Recipient</p>
            <p>{{ $log->recipient }}</p>
        </div>

        <div class="mt-4">
            <p class="font-bold mb-1">Subject</p>
            <p>{{ $log->subject }}</p>
        </div>

        <div class="mt-4">
            <p class="font-bold mb-1">Cc</p>
            @forelse (($log->cc ?: []) as $cc)
                @if ($cc->name)
                    <p>{{ $cc->name }} &lt;{{ $cc->email }}&gt;</p>
                @else
                    <p>{{ $cc->email }}</p>
                @endif
            @empty
                <p class="italic opacity-80 text-xs">No data available</p>
            @endforelse
        </div>

        <div class="mt-4">
            <p class="font-bold mb-1">Bcc</p>
            @forelse (($log->bcc ?: []) as $bcc)
                @if ($bcc->name)
                    <p>{{ $bcc->name }} &lt;{{ $bcc->email }}&gt;</p>
                @else
                    <p>{{ $bcc->email }}</p>
                @endif
            @empty
                <p class="italic opacity-80 text-xs">No data available</p>
            @endforelse
        </div>

        <div class="mt-4">
            <p class="font-bold mb-1">Template</p>
            {!! $template !!}
        </div>

        <div class="mt-4">
            <p class="font-bold mb-1">Variables</p>
            <pre wire:ignore id="log-variables" class="hljs w-full rounded p-3 shadow-sm focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70 border border-gray-300/50 dark:border-gray-600/50"><code class="language-json">{!! $variables !!}</code></pre>
        </div>
    </x-filament::section>

    <!-- Attachments -->
    <x-filament::section x-show="activeTab === 'attachments'">
        @if (count($log->attachments) === 0)
            <p class="italic text-xs opacity-80">No attachment available</p>
        @else
            <table class="w-full table-auto border-collapse text-left rtl:text-right divide-y dark:divide-gray-700 text-sm rounded-lg overflow-hidden">
                <thead>
                    <tr>
                        <th>
                            <span class="p-0 flex items-center w-full px-4 py-2 whitespace-nowrap space-x-1 rtl:space-x-reverse font-bold cursor-default">
                                Name
                            </span>
                        </th>
                        <th>
                            <span class="p-0 flex items-center w-full px-4 py-2 whitespace-nowrap space-x-1 rtl:space-x-reverse font-bold cursor-default">
                                Size
                            </span>
                        </th>
                        <th>
                            <span class="p-0 flex items-center w-full px-4 py-2 whitespace-nowrap space-x-1 rtl:space-x-reverse font-bold cursor-default">
                                Disk
                            </span>
                        </th>
                        <th class="w-5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y whitespace-nowrap dark:divide-gray-700">
                    @foreach ($log->attachments as $attachment)
                        <tr>
                            <td class="px-4 py-2 dark:text-white">
                                {{ $attachment->name }}
                            </td>
                            <td class="px-4 py-2 dark:text-white">
                                {{ $attachment->readableSize() }}
                            </td>
                            <td class="px-4 py-2 dark:text-white">
                                {{ $attachment->disk ?: '-' }}
                            </td>
                            <td class="px-4 py-2">
                                @if ($attachment->canBeDownloaded())
                                    <a
                                        href="{{ URL::route('download.attachment', $attachment) }}"
                                        class="font-medium text-primary-500 hover:text-primary-400"
                                        target="_blank"
                                        download>
                                        Download
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>

    <!-- Events -->
    <x-filament::section x-show="activeTab === 'events'">
        @if (count($log->events) === 0)
            <p class="italic text-xs opacity-80">No events available</p>
        @else
            <table class="w-full table-auto border-collapse text-left rtl:text-right divide-y dark:divide-gray-700 text-sm rounded-lg overflow-hidden">
                <thead>
                    <tr>
                        <th>
                            <span class="p-0 flex items-center w-full px-4 py-2 whitespace-nowrap space-x-1 rtl:space-x-reverse font-bold cursor-default">
                                Name
                            </span>
                        </th>
                        <th>
                            <span class="p-0 flex items-center w-full px-4 py-2 whitespace-nowrap space-x-1 rtl:space-x-reverse font-bold cursor-default">
                                Date
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y whitespace-nowrap dark:divide-gray-700">
                    @foreach ($log->events as $event)
                        <tr>
                            <td class="px-4 py-2 dark:text-white" style="width: 60%">
                                {{ $event->name }}
                            </td>
                            <td class="px-4 py-2 dark:text-white">
                                {{ $event->created_at->toDateTimeString() }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>
</div>

@assets
<link rel="stylesheet" href="{{ asset('vendor/mailcarrier/css/highlight.css') }}" />
@endassets
