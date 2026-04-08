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
    public function it_sends_mail_dashboard_and_whatsapp_for_approved_when_verified(): void
    {
        $user = User::factory()->create([
            'whatsapp' => '628111111111',
            'whatsapp_verified_at' => now(),
        ]);

        $notification = new EptSubmissionStatusNotification(status: 'approved');

        $channels = $notification->via($user);

        $this->assertEquals(['mail', 'database', 'whatsapp'], $channels);
        $this->assertSame(['database' => 'sync'], $notification->viaConnections());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sends_mail_and_dashboard_only_for_pending_even_when_whatsapp_verified(): void
    {
        $user = User::factory()->create([
            'whatsapp' => '628111111111',
            'whatsapp_verified_at' => now(),
        ]);

        $notification = new EptSubmissionStatusNotification(status: 'pending');

        $channels = $notification->via($user);

        $this->assertEquals(['mail', 'database'], $channels);
        $this->assertSame(['database' => 'sync'], $notification->viaConnections());
    }
}
