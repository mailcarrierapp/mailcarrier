<div
    class="filament-peek-panel filament-peek-editor"
    x-bind:style="editorStyle"
    x-ref="builderEditor"
    @if ($this->canAutoRefresh()) data-auto-refresh-strategy="{{ $this->autoRefreshStrategy }}" @endif
    @if ($this->shouldAutoRefresh()) data-should-auto-refresh="1" @endif
>
    <div class="filament-peek-panel-header">
        <div x-text="editorTitle"></div>
    </div>

    <div
        class="filament-peek-panel-body"
        x-on:focusout="onEditorFocusOut($event)"
    >
        <div
            x-bind:class="{
                'filament-peek-builder-editor': true,
                'has-sidebar-actions': editorHasSidebarActions,
            }"
        >
            <div class="filament-peek-builder-content">
                <form wire:submit="submit">
                    {{ $this->form }}

                    <button type="submit" style="display: none">
                        {{ __('filament-peek::ui.refresh-action-label') }}
                    </button>
                </form>

                <x-filament-actions::modals />
            </div>

            <div class="filament-peek-builder-actions"></div>

            <div
                class="filament-peek-editor-resizer"
                x-on:mousedown="onEditorResizerMouseDown($event)"
                x-bind:style="{display: editorIsResizable ? 'initial' : 'none'}"
            ></div>
        </div>
    </div>
</div>
