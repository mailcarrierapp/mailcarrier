<?php

namespace MailCarrier\Pages;

use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLoginPage;
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

    public function getHeading(): string | Htmlable
    {
        return '';
    }

    public function form(Form $form): Form
    {
        if (MailCarrier::getSocialAuthDriver()) {
            return $form->schema([]);
        }

        return parent::form($form);
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
