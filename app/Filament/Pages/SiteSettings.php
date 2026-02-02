<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Models\Prody;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
    public ?string $waBaseUrl = null;

    public function mount(): void
    {
        $this->waBaseUrl = rtrim(config('whatsapp.url', 'https://wa-api.lembagabahasa.site'), '/');
        $this->loadWaStatus();
        $this->loadWaLogs();
        
        $this->form->fill([
            'maintenance_mode' => SiteSetting::isMaintenanceEnabled(),
            'registration_enabled' => SiteSetting::isRegistrationEnabled(),
            'otp_enabled' => SiteSetting::isOtpEnabled(),
            'wa_notification_enabled' => SiteSetting::isWaNotificationEnabled(),
            'bl_quiz_enabled' => SiteSetting::isBlQuizEnabled(),
            'bl_period_start_date' => SiteSetting::getBlPeriodStartDate(),
            'bl_active_batch' => SiteSetting::get('bl_active_batch', now()->format('y')),
            'ept_all_prody' => SiteSetting::get('ept_all_prody', false),
            'ept_allowed_prody_ids' => SiteSetting::get('ept_allowed_prody_ids', []),
            'ept_allowed_prody_prefixes' => SiteSetting::get('ept_allowed_prody_prefixes', ['S2']),
            'ept_require_whatsapp' => SiteSetting::get('ept_require_whatsapp', false),
            'ept_require_role_pendaftar' => SiteSetting::get('ept_require_role_pendaftar', false),
            'ept_require_biodata' => SiteSetting::get('ept_require_biodata', false),
            'front_head_script' => SiteSetting::get('front_head_script', ''),
            'front_body_script' => SiteSetting::get('front_body_script', ''),
            'front_footer_script' => SiteSetting::get('front_footer_script', ''),
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

                        \Filament\Forms\Components\DatePicker::make('bl_period_start_date')
                            ->label('Tanggal Mulai Periode BL')
                            ->helperText('Data BL yang dibuat sebelum tanggal ini akan difilter dari tampilan default di admin panel')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->nullable(),

                        \Filament\Forms\Components\TextInput::make('bl_active_batch')
                            ->label('Angkatan BL Aktif')
                            ->helperText('Prefix NPM yang ditampilkan ke tutor. Bisa lebih dari satu, pisah dengan koma (contoh: 25,26)')
                            ->default(now()->format('y'))
                            ->maxLength(20)
                            ->required(),
                    ])
                    ->columns(1),

                Section::make('EPT Registration')
                    ->description('Atur syarat pendaftaran EPT dari dashboard pendaftar')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Toggle::make('ept_all_prody')
                            ->label('Semua Prodi Boleh Daftar')
                            ->helperText('Jika aktif, semua prodi bisa mendaftar EPT.')
                            ->onColor('success')
                            ->offColor('gray'),

                        Select::make('ept_allowed_prody_ids')
                            ->label('Daftar Prodi yang Diizinkan')
                            ->helperText('Jika diisi, hanya prodi ini yang boleh mendaftar. Jika kosong, akan pakai prefix.')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn () => Prody::query()->orderBy('name')->pluck('name', 'id'))
                            ->disabled(fn (Get $get) => (bool) $get('ept_all_prody')),

                        TagsInput::make('ept_allowed_prody_prefixes')
                            ->label('Prefix Prodi yang Diizinkan')
                            ->helperText('Contoh: S2, Profesi, Magister. Akan diabaikan jika daftar prodi diisi.')
                            ->placeholder('Tambah prefix')
                            ->disabled(fn (Get $get) => (bool) $get('ept_all_prody')),

                        Toggle::make('ept_require_whatsapp')
                            ->label('WhatsApp Wajib')
                            ->helperText('Jika aktif, user wajib mengisi (dan verifikasi jika OTP aktif) nomor WhatsApp.')
                            ->onColor('success')
                            ->offColor('gray'),

                        Toggle::make('ept_require_role_pendaftar')
                            ->label('Role Pendaftar Saja')
                            ->helperText('Jika aktif, hanya role pendaftar yang boleh mendaftar EPT.')
                            ->onColor('success')
                            ->offColor('gray'),

                        Toggle::make('ept_require_biodata')
                            ->label('Biodata Wajib')
                            ->helperText('Jika aktif, biodata harus lengkap sebelum bisa mendaftar EPT.')
                            ->onColor('success')
                            ->offColor('gray'),
                    ])
                    ->columns(1),

                Section::make('Script Front Site')
                    ->description('Tambahkan script tracking untuk halaman front site.')
                    ->icon('heroicon-o-code-bracket-square')
                    ->schema([
                        Textarea::make('front_head_script')
                            ->label('Script Head')
                            ->helperText('Akan disisipkan di <head> (contoh: tag <script> tracking).')
                            ->rows(5)
                            ->autosize(),

                        Textarea::make('front_body_script')
                            ->label('Script Body (Top)')
                            ->helperText('Akan disisipkan tepat setelah <body>. Cocok untuk <noscript>.')
                            ->rows(5)
                            ->autosize(),

                        Textarea::make('front_footer_script')
                            ->label('Script Footer')
                            ->helperText('Akan disisipkan sebelum </body>.')
                            ->rows(5)
                            ->autosize(),
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
        SiteSetting::set('bl_period_start_date', $data['bl_period_start_date'] ?? null);
        SiteSetting::set('bl_active_batch', $data['bl_active_batch'] ?? now()->format('y'));
        SiteSetting::set('ept_all_prody', $data['ept_all_prody'] ?? false);
        SiteSetting::set('ept_allowed_prody_ids', $data['ept_allowed_prody_ids'] ?? []);
        SiteSetting::set('ept_allowed_prody_prefixes', $data['ept_allowed_prody_prefixes'] ?? []);
        SiteSetting::set('ept_require_whatsapp', $data['ept_require_whatsapp'] ?? false);
        SiteSetting::set('ept_require_role_pendaftar', $data['ept_require_role_pendaftar'] ?? false);
        SiteSetting::set('ept_require_biodata', $data['ept_require_biodata'] ?? false);
        SiteSetting::set('front_head_script', $data['front_head_script'] ?? '');
        SiteSetting::set('front_body_script', $data['front_body_script'] ?? '');
        SiteSetting::set('front_footer_script', $data['front_footer_script'] ?? '');

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
        $this->waBaseUrl = rtrim(config('whatsapp.url', 'https://wa-api.lembagabahasa.site'), '/');
        $apiKey = (string) config('whatsapp.api_key', '');
        $enabled = (bool) config('whatsapp.enabled', false);
        $timeout = (int) config('whatsapp.timeout', 30);

        if (! $enabled) {
            $this->waStatus = [
                'status' => 'disabled',
                'message' => 'WhatsApp service nonaktif di konfigurasi',
            ];
            return;
        }

        if (blank($apiKey)) {
            $this->waStatus = [
                'status' => 'error',
                'message' => 'API key WhatsApp belum diatur',
            ];
            return;
        }

        try {
            $response = Http::timeout($timeout)
                ->withHeaders(['x-api-key' => $apiKey])
                ->get("{$this->waBaseUrl}/status");
            if ($response->successful()) {
                $data = $response->json();
                if (! is_array($data)) {
                    $this->waStatus = ['status' => 'error', 'message' => 'Format respons tidak valid'];
                    return;
                }

                if (! isset($data['status']) && array_key_exists('connected', $data)) {
                    $data['status'] = $data['connected'] ? 'connected' : 'disconnected';
                }

                $this->waStatus = $data;
            } else {
                $this->waStatus = [
                    'status' => 'error',
                    'message' => match ($response->status()) {
                        401 => 'Unauthorized (cek API key)',
                        404 => 'Endpoint status tidak ditemukan',
                        default => 'Gagal terhubung ke API',
                    },
                ];
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
        $this->waBaseUrl = rtrim(config('whatsapp.url', 'https://wa-api.lembagabahasa.site'), '/');
        $apiKey = (string) config('whatsapp.api_key', '');
        $enabled = (bool) config('whatsapp.enabled', false);
        $timeout = (int) config('whatsapp.timeout', 30);

        if (! $enabled || blank($apiKey)) {
            $this->waLogs = [];
            return;
        }

        try {
            $response = Http::timeout($timeout)
                ->withHeaders(['x-api-key' => $apiKey])
                ->get("{$this->waBaseUrl}/logs");
            if ($response->successful()) {
                $this->waLogs = $response->json('logs') ?? [];
            } else {
                $this->waLogs = [];
            }
        } catch (\Exception $e) {
            $this->waLogs = [];
        }
    }
}
