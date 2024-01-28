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
        <p class="font-bold mb-1">Variables</p>
        <pre x-ref="variables" class="hljs w-full rounded p-3 shadow-sm focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70 border border-gray-300/50 dark:border-gray-600/50"><code class="language-json">{!! $variables !!}</code></pre>
    </div>
</div>

@assets
<link rel="stylesheet" href="{{ asset('css/highlight.css') }}" />
@endassets
