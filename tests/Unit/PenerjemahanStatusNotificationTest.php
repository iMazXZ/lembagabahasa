<?php

namespace Tests\Unit;

use App\Models\User;
use App\Notifications\PenerjemahanStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenerjemahanStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prefers_whatsapp_when_verified(): void
    {
        $user = User::factory()->create([
            'whatsapp' => '628222222222',
            'whatsapp_verified_at' => now(),
        ]);

        $notification = new PenerjemahanStatusNotification(status: 'Selesai');

        $channels = $notification->via($user);

        $this->assertEquals(['whatsapp'], $channels);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_falls_back_to_mail_when_whatsapp_missing(): void
    {
        $user = User::factory()->create([
            'whatsapp' => null,
            'whatsapp_verified_at' => null,
        ]);

        $notification = new PenerjemahanStatusNotification(status: 'Diproses');

        $channels = $notification->via($user);

        $this->assertEquals(['mail'], $channels);
    }
}
