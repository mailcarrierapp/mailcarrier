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
        @foreach ($attachments as $attachment)
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
        @endforeach
    </tbody>
</table>
