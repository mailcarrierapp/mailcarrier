<?php

namespace MailCarrier\Resources\UserResource\Actions;

use Filament\Actions\CreateAction as BaseCreateAction;
use Filament\Forms;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use MailCarrier\Facades\MailCarrier;
use MailCarrier\Models\User;
use MailCarrier\Resources\UserResource;

class CreateAction extends BaseCreateAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize(fn (): bool => UserResource::canCreate());

        $this->modalFooterActionsAlignment(Alignment::End);
        $this->modalWidth(MaxWidth::Large);

        if (!is_null(MailCarrier::getSocialAuthDriver())) {
            $this->form([
                Forms\Components\Placeholder::make('cannot_create_users')
                    ->label('')
                    ->content(new HtmlString(<<<'HTML'
                        <div class="bg-warning-100 dark:bg-warning-500/20 border border-warning-300 dark:border-warning-600 rounded py-2 px-4">
                            Cannot create users with social authentication enabled.
                        </div>
                    HTML)),
            ]);

            $this->modalHeading('Cannot create users');
            $this->modalIcon('heroicon-o-exclamation-triangle');
            $this->modalIconColor('warning');
            $this->modalSubmitAction(false);
            $this->modalCancelActionLabel('Close');
            $this->extraModalFooterActions([]);

            return;
        }

        $this->form([
            Forms\Components\TextInput::make('name')
                ->required(),

            Forms\Components\TextInput::make('email')
                ->email()
                ->required()
                ->unique((new User)->getTable(), 'email'),

            Forms\Components\TextInput::make('password')
                ->password()
                ->revealable()
                ->required(),
        ]);

        $this->mutateFormDataUsing(function (array $data): array {
            $data['password'] = Hash::make($data['password']);

            return $data;
        });
    }
}
