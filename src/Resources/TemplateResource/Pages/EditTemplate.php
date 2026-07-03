<?php

namespace MailCarrier\Resources\TemplateResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\KeyValue;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MailCarrier\Actions\Templates\Preview;
use MailCarrier\Helpers\TemplateManager;
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
    protected function getHeaderActions(): array
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
        return [
            TemplateResource::getFormEditor()
                ->live()
                // The builder editor form has no bound record, so re-evaluate the lock
                // state from the editor data to keep the content read-only when locked.
                ->disabled(fn (Get $get): bool => (bool) $get('_isLocked'))
                ->afterStateUpdated(function (Get $get, string $state) {
                    Preview::cacheChanges(
                        $get('_internalId'),
                        Auth::user()->id,
                        $state,
                        $get('variables')
                    );
                }),

            KeyValue::make('variables')
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
        // When the template is locked, the "content" field is not dehydrated, so it's
        // missing from the editor data. Fall back to the raw form data in that case.
        $content = $editorData['content'] ?? $this->data['content'] ?? '';

        return [
            '_internalId' => $internalId = $this->getPreviewInternalId(),
            '_isLocked' => (bool) $this->getRecord()->is_locked,
            'variables' => Arr::mapWithKeys(
                TemplateManager::makeFromId($internalId, $content)->extractVariableNames(),
                fn (string $value) => [$value => null]
            ),
            ...$editorData,
            'content' => $content,
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
                $this->data['content'] ?? ''
            ),
        ]);
    }
}
