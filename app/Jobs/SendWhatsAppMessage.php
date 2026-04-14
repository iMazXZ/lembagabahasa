<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use App\Support\EptScheduleNotificationTracker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 200;

    public int $timeout = 50;

    public function __construct(
        public string $phone,
        public string $message,
        public ?array $tracking = null,
    ) {}

    public function middleware(): array
    {
        return [new RateLimited('wa-outbound')];
    }

    public function handle(WhatsAppService $waService): void
    {
        if (! $waService->isEnabled()) {
            EptScheduleNotificationTracker::markWhatsAppFailed(
                $this->tracking,
                'Service WhatsApp sedang nonaktif.'
            );
            return;
        }

        $sent = $waService->sendMessage($this->phone, $this->message);

        if (! $sent) {
            Log::warning("Failed to queue-send WhatsApp message to {$this->phone}");
            EptScheduleNotificationTracker::markWhatsAppFailed(
                $this->tracking,
                "Pesan WhatsApp ke {$this->phone} gagal dikirim oleh service."
            );
            return;
        }

        EptScheduleNotificationTracker::markWhatsAppSent($this->tracking);
    }

    public function failed(Throwable $exception): void
    {
        EptScheduleNotificationTracker::markWhatsAppFailed($this->tracking, $exception->getMessage());
    }
}
