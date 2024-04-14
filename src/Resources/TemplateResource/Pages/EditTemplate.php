<?php

namespace MailCarrier\Resources\TemplateResource\Pages;

use Filament\Actions;
use Filament\Forms\Components;
use Filament\Forms\Get;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MailCarrier\Actions\Templates\Preview;
use MailCarrier\Helpers\TemplateManager;
use MailCarrier\Livewire\PreviewTemplate;
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

    public static function getBuilderEditorSchema(): Components\Component|array
    {
        return [
            TemplateResource::getFormEditor()
                ->live()
                ->afterStateUpdated(function (Get $get, string $state) {
                    Preview::cacheChanges(
                        $get('_internalId'),
                        Auth::user()->id,
                        $state,
                        $get('variables')
                    );
                }),

            Components\KeyValue::make('variables')
                ->keyLabel('Variable name')
                ->valueLabel('Variable value')
                ->valuePlaceholder('Fill or delete')
                ->live()
                ->afterStateUpdated(function (Get $get, array $state) {
                    Preview::cacheChanges(
                        $get('_internalId'),
                        Auth::user()->id,
                        $get('content'),
                        $state
                    );
                }),
        ];
    }

    public function mutateInitialBuilderEditorData(string $builderName, array $editorData): array
    {
        return [
            '_internalId' => $internalId = $this->getPreviewInternalId(),
            'variables' => Arr::mapWithKeys(
                // @phpstan-ignore-next-line
                TemplateManager::makeFromId($internalId, $editorData['content'])->extractVariableNames(),
                fn (string $value) => [$value => null]
            ),
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
                $this->data['content']
            ),
        ]);
    }
}
