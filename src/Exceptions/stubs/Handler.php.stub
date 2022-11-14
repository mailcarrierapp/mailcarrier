<?php

namespace App\Exceptions;

use MailCarrier\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        parent::register();

        $this->reportable(function (\Throwable $e) {
            //
        });
    }
}
