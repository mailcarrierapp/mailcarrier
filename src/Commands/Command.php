<?php

namespace MailCarrier\Commands;

use Illuminate\Support\Facades\Validator;
use function Termwind\render;

abstract class Command extends \Illuminate\Console\Command
{
    /**
     * Validate an incoming input.
     *
     * @see https://github.com/filamentphp/filament/blob/2.x/packages/admin/src/Commands/Concerns/CanValidateInput.php
     */
    protected function validateInput(\Closure $callback, string $field, array $rules): ?string
    {
        $input = $callback();

        $validator = Validator::make(
            [$field => $input],
            [$field => $rules],
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            $input = $this->validateInput($callback, $field, $rules);
        }

        return $input;
    }

    /**
     * Show a success message for a task.
     */
    protected function labeledLine(string $line, string $label = 'DONE', string $bgColor = 'green-400', string $textColor = 'slate-600'): void
    {
        render(<<<HTML
            <div class="mx-2 mb-1">
                <span class="px-1 bg-$bgColor text-$textColor">$label</span>
                <span class="ml-1">$line</span>
            </div>
        HTML);
    }

    /**
     * Show a success alert.
     */
    protected function greenAlert(string $label): void
    {
        render(<<<HTML
            <div class="w-full mx-2 py-1 mt-1 bg-green-400 text-slate-800 text-center">
                $label
            </div>
        HTML);
    }
}
