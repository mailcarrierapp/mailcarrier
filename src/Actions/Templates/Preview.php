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

        return $this->render->run($template);
    }

    public static function cacheChanges(string $templateId, int $userId, string $content): string
    {
        $token = md5("template-{$templateId}-{$userId}");

        // Store the preview data in the cache to be retrieved from the Preview action
        $cacheData = [
            'templateId' => $templateId,
            'content' => $content,
        ];

        Cache::put('preview:' . $token, $cacheData, 5 * 60);

        return $token;
    }
}
