<?php

namespace MailCarrier\Resources\ApiTokenResource\Actions;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use MailCarrier\Actions\Auth\GenerateToken;
use MailCarrier\Resources\ApiTokenResource;

class CreateAction extends Action
{
    use CanCustomizeProcess;

    const GENERATED_TOKEN_FIELD_NAME = 'generated_token';

    public static function getDefaultName(): ?string
    {
        return 'create';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('filament-actions::create.single.label', [
            'label' => 'API Token',
        ]));

        $this->authorize(fn (): bool => ApiTokenResource::canCreate());

        $this->modalHeading(fn (): string => __('filament-actions::create.single.modal.heading', [
            'label' => 'API Token',
        ]));

        $this->modalSubmitActionLabel(__('filament-actions::create.single.modal.actions.create.label'));

        $this->successNotificationTitle(__('filament-actions::create.single.notifications.created.title'));

        $this->modalFooterActionsAlignment(Alignment::End);

        $this->modalWidth(MaxWidth::Large);

        $this->record(null);

        $this->form([
            Forms\Components\TextInput::make('name')
                ->hidden(fn (Forms\Get $get) => !is_null($get(static::GENERATED_TOKEN_FIELD_NAME)))
                ->required(),

            Forms\Components\DateTimePicker::make('expires_at')
                ->minDate(Carbon::now())
                ->label('Expiration date (UTC)')
                ->hidden(fn (Forms\Get $get) => !is_null($get(static::GENERATED_TOKEN_FIELD_NAME)))
                ->helperText('Leave empty to never expire'),

            Forms\Components\TextInput::make('generated_token')
                ->readOnly()
                ->dehydrated(false)
                ->helperText('Copy it to a safe place, it won\'t be shown anymore')
                ->extraInputAttributes([
                    'onClick' => 'this.select()',
                ])
                ->visible(fn (Forms\Get $get) => !is_null($get(static::GENERATED_TOKEN_FIELD_NAME))),
        ]);

        $this->action(function (array $data, Forms\Form $form): void {
            $form->fill([
                static::GENERATED_TOKEN_FIELD_NAME => GenerateToken::resolve()->run(
                    $data['name'],
                    expiresAt: $data['expires_at'],
                ),
            ]);

            $this->modalSubmitAction(false);
            $this->modalCancelActionLabel('Close');
            $this->sendSuccessNotification();
            $this->halt();
        });
    }
}
