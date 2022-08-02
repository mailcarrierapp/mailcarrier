<?php

namespace MailCarrier\Observers;

use MailCarrier\Actions\Templates;
use MailCarrier\Models\Template;

class TemplateObserver
{
    /**
     * Handle the Template "updated" event.
     */
    public function updated(Template $template): void
    {
        Templates\FindBySlug::flush($template->slug);
    }

    /**
     * Handle the Template "deleted" event.
     */
    public function deleted(Template $template): void
    {
        Templates\FindBySlug::flush($template->slug);
    }
}
