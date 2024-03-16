<?php

namespace MailCarrier\Resources\TemplateResource\Actions;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;
use MailCarrier\Actions\SendMail;
use MailCarrier\Dto\SendMailDto;
use MailCarrier\Helpers\TemplateManager;

class SendTestAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'send_test';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Send test');
        $this->icon('heroicon-o-paper-airplane');
        $this->modalHeading('Send test email');
        $this->modalSubmitActionLabel('Send');
        $this->modalFooterActionsAlignment(Alignment::End);
        $this->extraAttributes([
            'class' => 'button-send-test !bg-purple-500',
        ]);

        $this->form([
            Forms\Components\TextInput::make('email')
                ->email()
                ->required(),
            Forms\Components\KeyValue::make('variables')
                ->keyLabel('Variable name')
                ->valueLabel('Variable value')
                ->valuePlaceholder('Fill or delete')
                ->default(
                    Arr::mapWithKeys(
                        // @phpstan-ignore-next-line
                        TemplateManager::make($this->getRecord())->extractVariableNames(),
                        fn (string $value) => [$value => null]
                    )
                ),
            Forms\Components\Checkbox::make('enqueue'),
        ]);

        $this->action(function (array $data, Forms\Form $form): void {
            // Reset error box if any
            $this->modalContent(null);

            try {
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
            } catch (\Exception $e) {
                $this->modalContent(new HtmlString(<<<HTML
                    <div class="bg-danger-100 dark:bg-danger-500/20 border border-danger-300 dark:border-danger-600 rounded py-3 px-4 text-sm">
                        <p class="font-bold">🤕 Something went wrong</p>
                        <p class="mt-2 text-xs">{$e->getMessage()}</p>
                    </div>
                HTML));

                $this->halt();

                return;
            }

            Notification::make()
                ->title('Test email sent correctly')
                ->icon('heroicon-o-envelope')
                ->success()
                ->send();
        });
    }
}
