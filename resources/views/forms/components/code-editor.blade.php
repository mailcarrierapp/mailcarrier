<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="codeEditor" class="w-full max-h-full rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500 overflow-hidden">
        <div x-ref="editor" class="w-full h-[300px]" wire:ignore></div>
    </div>
</x-dynamic-component>

<script type="module">
    import * as monaco from 'https://cdn.jsdelivr.net/npm/monaco-editor@0.39.0/+esm';

    Alpine.data('codeEditor', () => ({
        state: @entangle($getStatePath()),
        init() {
            const editor = monaco.editor.create(this.$refs.editor, {
                value: this.state,
                language: 'twig',
                automaticLayout: true,
                scrollBeyondLastLine: false,
                readOnly: {{ $isDisabled() ? 'true' : 'false' }},
                minimap: {
                    enabled: false,
                },
            });

            editor.getModel().onDidChangeContent(() => this.state = editor.getValue());

            window.addEventListener('theme-changed', () => {
                let theme = localStorage.getItem('theme');

                if (theme === 'system') {
                    theme = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)
                        ? 'dark'
                        : 'light';
                }

                monaco.editor.setTheme(theme === 'light' ? 'vs' : 'vs-dark');
            });
        }
    }));
</script>
