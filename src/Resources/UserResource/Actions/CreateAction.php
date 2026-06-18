<?php

namespace MailCarrier\Resources\UserResource\Actions;

use Filament\Actions\CreateAction as BaseCreateAction;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
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
        $this->modalWidth(Width::Large);

        if (!is_null(MailCarrier::getSocialAuthDriver())) {
            $this->schema([
                TextEntry::make('_social_auth_notice')
                    ->label('')
                    ->state('')
                    ->formatStateUsing(fn (): HtmlString => new HtmlString(<<<'HTML'
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

        $this->schema([
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

        $this->mutateDataUsing(function (array $data): array {
            $data['password'] = Hash::make($data['password']);

            return $data;
        });
    }
}
