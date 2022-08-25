<?php

namespace MailCarrier\Resources\TemplateResource\Pages;

use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use MailCarrier\Actions\SendMail;
use MailCarrier\Dto\SendMailDto;
use MailCarrier\Resources\TemplateResource;

class EditTemplate extends EditRecord
{
    protected static string $resource = TemplateResource::class;

    /** @var \MailCarrier\Models\Template */
    public $record;

    /**
     * Get resource top-right actions.
     */
    protected function getActions(): array
    {
        return [
            Action::make('send_test')
                ->label('Send test')
                ->icon('heroicon-o-paper-airplane')
                ->extraAttributes([
                    'class' => 'has-paper-airplane-icon !bg-purple-500',
                ])
                // Build the modal
                ->action($this->sendTestMail(...))
                ->modalButton('Send')
                ->form([
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required(),
                    Forms\Components\KeyValue::make('variables')
                        ->keyLabel('Variable name')
                        ->valueLabel('Variable value'),
                    Forms\Components\Checkbox::make('enqueue'),
                ]),
            DeleteAction::make()
                ->disabled($this->record->is_locked || !TemplateResource::canDelete($this->record)),
        ];
    }

    /**
     * Send a test mail.
     */
    protected function sendTestMail(array $data): void
    {
        SendMail::resolve()
            ->withoutLogging()
            ->run(
                new SendMailDto(
                    template: $this->getRecord()->slug, // @phpstan-ignore-line
                    subject: 'Test from ' . Config::get('app.name'),
                    recipient: $data['email'],
                    enqueue: $data['enqueue'],
                    variables: Arr::undot($data['variables'] ?: []),
                )
            );

        Notification::make()
            ->title('Test email sent correctly')
            ->icon('heroicon-o-mail')
            ->iconColor('success')
            ->send();
    }
}
