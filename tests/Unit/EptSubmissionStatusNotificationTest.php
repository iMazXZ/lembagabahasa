<?php

namespace Tests\Unit;

use App\Models\User;
use App\Notifications\EptSubmissionStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EptSubmissionStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prefers_whatsapp_when_verified(): void
    {
        $user = User::factory()->create([
            'whatsapp' => '628111111111',
            'whatsapp_verified_at' => now(),
        ]);

        $notification = new EptSubmissionStatusNotification(status: 'approved');

        $channels = $notification->via($user);

        $this->assertEquals(['whatsapp'], $channels);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_falls_back_to_mail_when_whatsapp_not_verified(): void
    {
        $user = User::factory()->create([
            'whatsapp' => '628111111111',
            'whatsapp_verified_at' => null,
        ]);

        $notification = new EptSubmissionStatusNotification(status: 'pending');

        $channels = $notification->via($user);

        $this->assertEquals(['mail'], $channels);
    }
}
