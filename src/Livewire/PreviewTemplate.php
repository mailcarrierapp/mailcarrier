<?php

namespace MailCarrier\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use MailCarrier\Actions\Templates\Preview;

class PreviewTemplate extends Component
{
    #[Locked]
    public string $token;

    #[Locked]
    public string $previewContent = '';

    public function mount(Request $request): void
    {
        $this->token = $request->query('token') ?: throw new \Exception('No preview token provided.');
    }

    #[Layout('mailcarrier::livewire.layout')]
    public function render(Preview $preview): string
    {
        $content = $preview->run(Cache::get('preview:' . $this->token));

        return <<<HTML
            <div wire:poll.1s>
                {$content}
            </div>
        HTML;
    }
}
