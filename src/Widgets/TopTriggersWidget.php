<?php

namespace MailCarrier\Widgets;

use Filament\Widgets\Widget;
use MailCarrier\Actions\Logs\Widgets\GetTopTriggers;

class TopTriggersWidget extends Widget
{
    protected static string $view = 'mailcarrier::widgets.top-triggers';

    /**
     * Get widget title.
     */
    protected function getHeading(): string
    {
        return 'Top triggers';
    }

    /**
     * Get widget data.
     */
    protected function getViewData(): array
    {
        return [
            'heading' => $this->getHeading(),
            'data' => GetTopTriggers::resolve()->run(),
        ];
    }
}
