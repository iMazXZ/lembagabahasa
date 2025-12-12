<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Support\Facades\Http;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Situs';
    protected static ?string $title = 'Pengaturan Situs';
    protected static ?string $slug = 'site-settings';
    protected static ?int $navigationSort = 100;
    protected static ?string $navigationGroup = 'Sistem';

    protected static string $view = 'filament.pages.site-settings';

    public function getMaxContentWidth(): \Filament\Support\Enums\MaxWidth
    {
        return \Filament\Support\Enums\MaxWidth::Full;
    }

    public ?array $data = [];
    public ?array $waStatus = null;
    public ?array $waLogs = [];

    public function mount(): void
    {
        $this->loadWaStatus();
        $this->loadWaLogs();
        
        $this->form->fill([
            'maintenance_mode' => SiteSetting::isMaintenanceEnabled(),
            'registration_enabled' => SiteSetting::isRegistrationEnabled(),
            'otp_enabled' => SiteSetting::isOtpEnabled(),
            'wa_notification_enabled' => SiteSetting::isWaNotificationEnabled(),
            'bl_quiz_enabled' => SiteSetting::isBlQuizEnabled(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Umum')
                    ->description('Pengaturan umum sistem')
                    ->icon('heroicon-o-cog')
                    ->schema([
                        Toggle::make('maintenance_mode')
                            ->label('Maintenance Mode')
                            ->helperText('Jika aktif, tampilkan halaman maintenance untuk semua user (kecuali admin)')
                            ->onColor('danger')
                            ->offColor('gray'),

                        Toggle::make('registration_enabled')
                            ->label('Registrasi Terbuka')
                            ->helperText('Jika nonaktif, user baru tidak bisa mendaftar')
                            ->onColor('success')
                            ->offColor('danger'),
                    ])
                    ->columns(1),

                Section::make('WhatsApp & OTP')
                    ->description('Pengaturan integrasi WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        Toggle::make('otp_enabled')
                            ->label('OTP WhatsApp')
                            ->helperText('Jika aktif, user harus verifikasi nomor via kode OTP yang dikirim ke WhatsApp. Matikan ini jika WhatsApp terkena limit/ban.')
                            ->onColor('success')
                            ->offColor('gray'),

                        Toggle::make('wa_notification_enabled')
                            ->label('Notifikasi WhatsApp')
                            ->helperText('Jika aktif, sistem mengirim notifikasi status (EPT, Penerjemahan) via WhatsApp')
                            ->onColor('success')
                            ->offColor('gray'),
                    ])
                    ->columns(1),

                Section::make('Basic Listening')
                    ->description('Pengaturan fitur Basic Listening')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Toggle::make('bl_quiz_enabled')
                            ->label('Fitur Quiz Aktif')
                            ->helperText('Jika nonaktif, user tidak bisa mengakses quiz Basic Listening')
                            ->onColor('success')
                            ->offColor('danger'),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::set('maintenance_mode', $data['maintenance_mode'] ?? false);
        SiteSetting::set('registration_enabled', $data['registration_enabled'] ?? true);
        SiteSetting::set('otp_enabled', $data['otp_enabled'] ?? false);
        SiteSetting::set('wa_notification_enabled', $data['wa_notification_enabled'] ?? true);
        SiteSetting::set('bl_quiz_enabled', $data['bl_quiz_enabled'] ?? true);

        // Clear all cache
        SiteSetting::clearCache();

        Notification::make()
            ->title('Pengaturan berhasil disimpan!')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function loadWaStatus(): void
    {
        try {
            $response = Http::timeout(5)->get('https://wa-api.lembagabahasa.site/status');
            if ($response->successful()) {
                $this->waStatus = $response->json();
            } else {
                $this->waStatus = ['status' => 'error', 'message' => 'Gagal terhubung ke API'];
            }
        } catch (\Exception $e) {
            $this->waStatus = ['status' => 'error', 'message' => 'Timeout atau error: ' . $e->getMessage()];
        }
    }

    public function refreshWaStatus(): void
    {
        $this->loadWaStatus();
        $this->loadWaLogs();
        
        Notification::make()
            ->title('Status WhatsApp diperbarui')
            ->success()
            ->send();
    }

    public function loadWaLogs(): void
    {
        try {
            $response = Http::timeout(5)->get('https://wa-api.lembagabahasa.site/logs');
            if ($response->successful()) {
                $this->waLogs = $response->json()['logs'] ?? [];
            } else {
                $this->waLogs = [];
            }
        } catch (\Exception $e) {
            $this->waLogs = [];
        }
    }
}
