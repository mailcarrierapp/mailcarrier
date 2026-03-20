<?php

namespace MailCarrier\Pages;

use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLoginPage;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use MailCarrier\Facades\MailCarrier;

class Login extends BaseLoginPage
{
    public function authenticate(): ?LoginResponse
    {
        if (MailCarrier::getSocialAuthDriver()) {
            return null;
        }

        return parent::authenticate();
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function form(Schema $schema): Schema
    {
        if (MailCarrier::getSocialAuthDriver()) {
            return $schema->components([]);
        }

        return parent::form($schema);
    }

    protected function getFormActions(): array
    {
        if (MailCarrier::getSocialAuthDriver()) {
            return [
                Action::make('login')
                    ->label(__('filament-panels::pages/auth/login.form.actions.authenticate.label'))
                    ->action(fn () => redirect()->route('auth.redirect')),
            ];
        }

        return parent::getFormActions();
    }
}
