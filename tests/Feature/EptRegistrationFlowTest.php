<?php

namespace Tests\Feature;

use App\Models\EptRegistration;
use App\Models\EptGroup;
use App\Models\SiteSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EptRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_blocks_new_registration_when_user_already_has_active_ept_registration(): void
    {
        SiteSetting::query()->updateOrCreate(
            ['key' => 'ept_all_prody'],
            [
                'value' => '1',
                'type' => 'boolean',
                'group' => 'ept',
                'label' => 'EPT Semua Prodi',
                'description' => 'Aktif untuk test',
            ],
        );

        $user = User::factory()->create();

        EptRegistration::query()->create([
            'user_id' => $user->id,
            'bukti_pembayaran' => 'ept-registrations/payments/existing.webp',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard.ept-registration.index'))
            ->post(route('dashboard.ept-registration.store'), [
                'student_status' => EptRegistration::STUDENT_STATUS_REGULAR,
                'bukti_pembayaran' => UploadedFile::fake()->image('payment.jpg'),
            ]);

        $response->assertRedirect(route('dashboard.ept-registration.index'));
        $response->assertSessionHas('error', 'Anda sudah memiliki pendaftaran aktif.');

        $this->assertSame(1, EptRegistration::query()->where('user_id', $user->id)->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_distinct_group_assignments(): void
    {
        $this->assertTrue(EptRegistration::hasDistinctGroupAssignments([1, 2, 3]));
        $this->assertFalse(EptRegistration::hasDistinctGroupAssignments([1, 1, 3]));
        $this->assertFalse(EptRegistration::hasDistinctGroupAssignments([2, 3, 2]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_student_status_when_registering_for_ept(): void
    {
        SiteSetting::query()->updateOrCreate(
            ['key' => 'ept_all_prody'],
            [
                'value' => '1',
                'type' => 'boolean',
                'group' => 'ept',
                'label' => 'EPT Semua Prodi',
                'description' => 'Aktif untuk test',
            ],
        );

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard.ept-registration.index'))
            ->post(route('dashboard.ept-registration.store'), [
                'bukti_pembayaran' => UploadedFile::fake()->image('payment.jpg'),
            ]);

        $response->assertRedirect(route('dashboard.ept-registration.index'));
        $response->assertSessionHasErrors('student_status');
        $this->assertSame(0, EptRegistration::query()->where('user_id', $user->id)->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_still_blocks_new_registration_when_previous_approved_cycle_is_not_finished(): void
    {
        SiteSetting::query()->updateOrCreate(
            ['key' => 'ept_all_prody'],
            [
                'value' => '1',
                'type' => 'boolean',
                'group' => 'ept',
                'label' => 'EPT Semua Prodi',
                'description' => 'Aktif untuk test',
            ],
        );

        Carbon::setTestNow('2026-03-03 10:00:00');

        $user = User::factory()->create();
        $group1 = EptGroup::query()->create(['name' => '01 E 25/26', 'jadwal' => now()->subDay(), 'lokasi' => 'Lab 1']);
        $group2 = EptGroup::query()->create(['name' => '02 E 25/26', 'jadwal' => now()->addDay(), 'lokasi' => 'Lab 2']);
        $group3 = EptGroup::query()->create(['name' => '03 E 25/26', 'jadwal' => now()->addDays(2), 'lokasi' => 'Lab 3']);

        EptRegistration::query()->create([
            'user_id' => $user->id,
            'student_status' => EptRegistration::STUDENT_STATUS_REGULAR,
            'bukti_pembayaran' => 'ept-registrations/payments/existing.webp',
            'status' => 'approved',
            'grup_1_id' => $group1->id,
            'grup_2_id' => $group2->id,
            'grup_3_id' => $group3->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard.ept-registration.index'))
            ->post(route('dashboard.ept-registration.store'), [
                'student_status' => EptRegistration::STUDENT_STATUS_REGULAR,
                'bukti_pembayaran' => UploadedFile::fake()->image('payment.jpg'),
            ]);

        $response->assertRedirect(route('dashboard.ept-registration.index'));
        $response->assertSessionHas('error', 'Anda sudah memiliki pendaftaran aktif.');

        $this->assertSame(1, EptRegistration::query()->where('user_id', $user->id)->count());

        Carbon::setTestNow();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_allows_new_registration_after_all_required_schedules_have_finished(): void
    {
        SiteSetting::query()->updateOrCreate(
            ['key' => 'ept_all_prody'],
            [
                'value' => '1',
                'type' => 'boolean',
                'group' => 'ept',
                'label' => 'EPT Semua Prodi',
                'description' => 'Aktif untuk test',
            ],
        );

        Carbon::setTestNow('2026-03-03 10:00:00');

        $user = User::factory()->create();
        $group1 = EptGroup::query()->create(['name' => '01 E 25/26', 'jadwal' => now()->subDays(3), 'lokasi' => 'Lab 1']);
        $group2 = EptGroup::query()->create(['name' => '02 E 25/26', 'jadwal' => now()->subDays(2), 'lokasi' => 'Lab 2']);
        $group3 = EptGroup::query()->create(['name' => '03 E 25/26', 'jadwal' => now()->subDay(), 'lokasi' => 'Lab 3']);

        EptRegistration::query()->create([
            'user_id' => $user->id,
            'student_status' => EptRegistration::STUDENT_STATUS_REGULAR,
            'bukti_pembayaran' => 'ept-registrations/payments/existing.webp',
            'status' => 'approved',
            'grup_1_id' => $group1->id,
            'grup_2_id' => $group2->id,
            'grup_3_id' => $group3->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.ept-registration.store'), [
                'student_status' => EptRegistration::STUDENT_STATUS_REGULAR,
                'bukti_pembayaran' => UploadedFile::fake()->image('payment.jpg'),
            ]);

        $response->assertRedirect(route('dashboard.ept-registration.index'));
        $response->assertSessionHas('success');

        $this->assertSame(2, EptRegistration::query()->where('user_id', $user->id)->count());
        $this->assertSame(1, EptRegistration::query()->where('user_id', $user->id)->pending()->count());

        Carbon::setTestNow();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function general_participant_only_requires_one_group_assignment(): void
    {
        $registration = EptRegistration::query()->make([
            'student_status' => EptRegistration::STUDENT_STATUS_GENERAL,
        ]);

        $this->assertTrue($registration->isGeneralParticipant());
        $this->assertSame(1, $registration->requiredGroupCount());
        $this->assertTrue(EptRegistration::hasDistinctGroupAssignments([10]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_payment_proof_file_when_registration_is_deleted(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $path = 'ept-registrations/payments/delete-me.webp';

        Storage::disk('public')->put($path, 'fake-image');

        $registration = EptRegistration::query()->create([
            'user_id' => $user->id,
            'student_status' => EptRegistration::STUDENT_STATUS_REGULAR,
            'bukti_pembayaran' => $path,
            'status' => 'pending',
        ]);

        Storage::disk('public')->assertExists($path);

        $registration->delete();

        Storage::disk('public')->assertMissing($path);
    }
}
