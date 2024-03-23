<?php

namespace MailCarrier\Resources\TemplateResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
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
    use HasPreviewModal;
    use HasBuilderPreview;

    protected static string $resource = TemplateResource::class;
    protected static string $recordId;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        static::$recordId = $this->getRecord()->id;
    }

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
            ->afterStateUpdated(function (Get $get, string $state) {
                dump(
                    $get('_internalId'),
                    Auth::user()->id,
                    $state
                );

                Preview::cacheChanges(
                    $get('_internalId'),
                    Auth::user()->id,
                    $state
                );
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

    protected function getBuilderPreviewUrl(string $builderName): ?string
    {
        // Generate a unique token for this preview
        $templateId = $this->data['id'] ?? uniqid();
        $userId = Auth::user()->id;

        dump(
            $templateId, $userId, $this->data['name']
        );

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
