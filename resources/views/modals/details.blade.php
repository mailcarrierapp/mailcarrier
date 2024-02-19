<div
    class="text-sm"
    x-init="
        if (localStorage.getItem('theme') === 'light') {
            $refs.variables.classList.add('theme-light');
        } else {
            $refs.variables.classList.add('theme-dark');
        }

        hljs.highlightAll()">
    <div>
        <p class="font-bold mb-1">Sender</p>
        @if ($log->sender->name)
            <p>{{ $log->sender->name }} &lt;{{ $log->sender->email }}&gt;</p>
        @else
            <p>{{ $log->sender->email }}</p>
        @endif
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
        <p class="font-bold mb-1">Attachments</p>
        <table class="w-full table-auto border-collapse text-left rtl:text-right divide-y dark:divide-gray-700 text-sm rounded-lg overflow-hidden">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-500/10">
                    <th>
                        <span class="p-0 flex items-center w-full px-4 py-2 whitespace-nowrap space-x-1 rtl:space-x-reverse font-medium text-gray-600 dark:text-gray-300 cursor-default">
                            Name
                        </span>
                    </th>
                    <th>
                        <span class="p-0 flex items-center w-full px-4 py-2 whitespace-nowrap space-x-1 rtl:space-x-reverse font-medium text-gray-600 dark:text-gray-300 cursor-default">
                            Size
                        </span>
                    </th>
                    <th>
                        <span class="p-0 flex items-center w-full px-4 py-2 whitespace-nowrap space-x-1 rtl:space-x-reverse font-medium text-gray-600 dark:text-gray-300 cursor-default">
                            Disk
                        </span>
                    </th>
                    <th class="w-5"></th>
                </tr>
            </thead>
            <tbody class="divide-y whitespace-nowrap dark:divide-gray-700">
                @forelse ($log->attachments as $attachment)
                    <tr class="dark:bg-gray-700/50 odd:bg-white even:bg-gray-50 even:dark:bg-gray-700/80">
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
                @empty
                    <tr class="dark:bg-gray-700/50 odd:bg-white even:bg-gray-50 even:dark:bg-gray-700/80">
                        <td class="px-4 py-2 italic text-xs opacity-80" colspan="4">No attachment available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <p class="font-bold mb-1">Variables</p>
        <pre wire:ignore x-ref="variables" class="hljs w-full rounded p-3 shadow-sm focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70 border border-gray-300/50 dark:border-gray-600/50"><code class="language-json">{!! $variables !!}</code></pre>
    </div>
</div>

@assets
<link rel="stylesheet" href="{{ asset('vendor/mailcarrier/css/highlight.css') }}" />
@endassets
