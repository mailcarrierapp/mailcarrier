<x-forms::field-wrapper
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }">
        <div
            wire:ignore
            class="w-full rounded-lg shadow-sm focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70 border-gray-300 dark:border-gray-600 overflow-hidden"
            style="min-height: 400px"
            x-init="
                document.addEventListener('DOMContentLoaded', () => {
                    const editor = monaco.editor.create($el, {
                        value: state,
                        language: 'twig',
                        automaticLayout: true,
                        scrollBeyondLastLine: false,
                        theme: 'vs-dark',
                        readOnly: {{ $isDisabled() ? 'true' : 'false' }},
                        minimap: {
                            enabled: false,
                        },
                    });

                    editor.getModel().onDidChangeContent(() => state = editor.getValue());
                });
            ">
        </div>
    </div>
</x-forms::field-wrapper>
