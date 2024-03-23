<?php

namespace MailCarrier\Resources\TemplateResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Component;
use Filament\Forms\Get;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use MailCarrier\Actions\Templates\Preview;
use MailCarrier\Models\Template;
use MailCarrier\Resources\TemplateResource;
use MailCarrier\Resources\TemplateResource\Actions\SendTestAction;
use Pboivin\FilamentPeek\Pages\Concerns\HasBuilderPreview;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;

class EditTemplate extends EditRecord
{
    use HasBuilderPreview;
    use HasPreviewModal;

    protected static string $resource = TemplateResource::class;

    public function getRecord(): Template
    {
        return $this->record;
    }

    /**
     * Get resource top-right actions.
     */
    protected function getActions(): array
    {
        return [
            SendTestAction::make(),
            Actions\Action::make('save')
                ->label(__('Save changes'))
                ->action('save'),
        ];
    }

    /**
     * Get resource after-form actions.
     */
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label(__('Save changes'))
                ->action('save'),

            Actions\DeleteAction::make()
                ->disabled($this->getRecord()->is_locked || !TemplateResource::canDelete($this->record)),
        ];
    }

    public static function getBuilderEditorSchema(): Component|array
    {
        return TemplateResource::getFormEditor()
            ->afterStateUpdated(function (Get $get, string $state, \Livewire\Component $livewire) {
                Preview::cacheChanges(
                    $get('_internalId'),
                    Auth::user()->id,
                    $state
                );

                $livewire->js("
                    document.querySelector('.filament-peek-panel-body iframe').contentWindow.location.reload();
                ");
            });
    }

    public function mutateInitialBuilderEditorData(string $builderName, array $editorData): array
    {
        return [
            '_internalId' => $this->getPreviewInternalId(),
            ...$editorData,
        ];
    }

    protected function getPreviewInternalId(): string|int
    {
        return $this->data['id'] ?? uniqid();
    }

    protected function getBuilderPreviewUrl(): ?string
    {
        // Return the preview URL
        return route('templates.preview', [
            'token' => Preview::cacheChanges(
                $this->getPreviewInternalId(),
                Auth::user()->id,
                $this->data['name']
            ),
        ]);
    }
}
