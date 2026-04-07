<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $phone,
        public string $message,
    ) {}

    public function middleware(): array
    {
        return [new RateLimited('wa-outbound')];
    }

    public function handle(WhatsAppService $waService): void
    {
        if (! $waService->isEnabled()) {
            return;
        }

        $sent = $waService->sendMessage($this->phone, $this->message);

        if (! $sent) {
            Log::warning("Failed to queue-send WhatsApp message to {$this->phone}");
        }
    }
}
