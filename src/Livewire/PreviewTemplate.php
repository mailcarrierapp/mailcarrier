<?php

namespace MailCarrier\Livewire;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Locked;
use Livewire\Component;
use MailCarrier\Actions\Templates\Preview;

class PreviewTemplate extends Component
{
    #[Locked]
    public string $token;

    #[Locked]
    public string $previewContent = '';

    public function mount(string $token): void
    {
        $this->token = $token;
    }

    public function render(Preview $preview): string
    {
        $content = $preview->run(Cache::get('preview:' . $this->token));

        return <<<HTML
            <div wire:poll.500ms>
                {$content}
            </div>
        HTML;
    }
}
