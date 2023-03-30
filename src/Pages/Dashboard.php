<?php

namespace MailCarrier\Pages;

use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    protected function getColumns(): int|array
    {
        return 3;
    }
}
