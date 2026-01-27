<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppOtp;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WhatsAppOtpTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_dispatches_job_when_sending_otp(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        SiteSetting::updateOrCreate(
            ['key' => 'otp_enabled'],
            ['value' => '1', 'type' => 'boolean', 'group' => 'whatsapp', 'label' => 'OTP Enabled']
        );

        $response = $this
            ->actingAs($user)
            ->post('/api/whatsapp/send-otp', ['whatsapp' => '081234567890']);

        $response->assertStatus(200)->assertJson(['success' => true]);
        Queue::assertPushed(SendWhatsAppOtp::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_rejects_duplicate_whatsapp_number(): void
    {
        $existing = User::factory()->create(['whatsapp' => '6281234567890']);
        $user = User::factory()->create();
        SiteSetting::updateOrCreate(
            ['key' => 'otp_enabled'],
            ['value' => '1', 'type' => 'boolean', 'group' => 'whatsapp', 'label' => 'OTP Enabled']
        );

        $response = $this
            ->actingAs($user)
            ->post('/api/whatsapp/send-otp', ['whatsapp' => '081234567890']);

        $response->assertStatus(422);
        $this->assertEquals($existing->id, $existing->fresh()->id); // ensure not touched
    }
}
