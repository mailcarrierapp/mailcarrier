<?php

namespace MailCarrier\Actions\Templates;

use Illuminate\Support\Facades\Cache;
use MailCarrier\Actions\Action;
use MailCarrier\Models\Template;

class Preview extends Action
{
    public function __construct(private readonly Render $render)
    {
        //
    }

    /**
     * Render a template with the given variables.
     */
    public function run(array $data): string
    {
        $template = Template::find($data['templateId']) ?: new Template();
        $template->content = $data['content'];

        return rescue(
            fn () => $this->render
            ->setStrictVariables(false)
            ->run($template, $data['variables']),
            rescue: '',
            report: false
        );
    }

    public static function cacheChanges(
        string $templateId,
        int $userId,
        string $content,
        array $variables = []
    ): string {
        $token = md5("template-{$templateId}-{$userId}");

        // Store the preview data in the cache to be retrieved from the Preview action
        $cacheData = [
            'templateId' => $templateId,
            'content' => $content,
            'variables' => $variables,
        ];

        Cache::put('preview:' . $token, $cacheData, 60 * 60);

        return $token;
    }
}
