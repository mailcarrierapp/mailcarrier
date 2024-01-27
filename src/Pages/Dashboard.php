<?php

namespace MailCarrier\Pages;

use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    public function getColumns(): int | string | array
    {
        return 3;
    }
}
