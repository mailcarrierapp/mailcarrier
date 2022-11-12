<?php

namespace MailCarrier\Observers;

use MailCarrier\Actions\Templates;

class LayoutObserver
{
    /**
     * Handle the Layout "updated" event.
     */
    public function updated(): void
    {
        Templates\FindBySlug::flushAll();
    }

    /**
     * Handle the Layout "deleted" event.
     */
    public function deleted(): void
    {
        Templates\FindBySlug::flushAll();
    }
}
