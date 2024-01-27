<div class="text-sm">
    <div>
        <p class="font-bold mb-1">Sender</p>
        @if ($log->sender->name)
            <p>{{ $log->sender->name }} &lt;{{ $log->sender->email }}&gt;</p>
        @else
            <p>{{ $log->sender->email }}</p>
        @endif
    </div>

    @if ($log->cc)
        <div class="mt-4">
            <p class="font-bold mb-1">Cc</p>
            @foreach ($log->cc as $cc)
                @if ($cc->name)
                    <p>{{ $cc->name }} &lt;{{ $cc->email }}&gt;</p>
                @else
                    <p>{{ $cc->email }}</p>
                @endif
            @endforeach
        </div>
    @endif

    @if ($log->bcc)
        <div class="mt-4">
            <p class="font-bold mb-1">Bcc</p>
            @foreach ($log->bcc as $bcc)
                @if ($bcc->name)
                    <p>{{ $bcc->name }} &lt;{{ $bcc->email }}&gt;</p>
                @else
                    <p>{{ $bcc->email }}</p>
                @endif
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        <p class="font-bold mb-1">Variables</p>
        <div
            id="editor"
            class="w-full rounded-lg shadow-sm focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70 border-gray-300 dark:border-gray-600 overflow-hidden"
            style="min-height: 200px"
            wire:ignore>
        </div>
    </div>

    <script wire:ignore>
    monaco.editor.create(document.getElementById('editor'), {
        value: `{!! $variables !!}`,
        language: 'json',
        readOnly: true,
        automaticLayout: true,
        scrollBeyondLastLine: false,
        theme: 'vs-dark',
        minimap: {
            enabled: false,
        },
    });
    </script>
</div>
