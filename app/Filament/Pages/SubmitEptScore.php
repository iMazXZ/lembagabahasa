<?php

namespace App\Filament\Pages;

use App\Models\EptSubmission;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

// ==== Forms ====
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Actions as FormActions;
use Filament\Forms\Components\Actions\Action as FormAction;

// ==== Tables ====
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;

// ==== Page / Panel Actions ====
use Filament\Actions\Action as PageAction;

// ==== Misc ====
use Filament\Notifications\Notification;

class SubmitEptScore extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'Pengajuan Surat Rekomendasi';
    protected static ?string $title           = ' ';
    protected static ?string $navigationGroup = 'Layanan Lembaga Bahasa';
    protected static string  $view            = 'filament.pages.submit-ept-score';

    /** sembunyikan form jika user sudah punya pengajuan (pending/approved) */
    public bool $hasSubmissions = false;

    /** state form */
    public ?array $data = [];

    public bool $hasApproved = false;
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('pendaftar');
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('pendaftar');
    }

    protected function userHasCompleteBiodata(): bool
    {
        $u = Auth::user();

        return $u
            && $u->prody !== null && $u->prody !== ''
            && $u->srn   !== null && $u->srn   !== ''
            && $u->year  !== null && $u->year  !== ''
            && ! is_null($u->nilaibasiclistening); // 0 tetap valid
    }

    public function mount(): void
    {
        $this->form->fill();

        $this->hasSubmissions = EptSubmission::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        $this->hasApproved = EptSubmission::where('user_id', Auth::id())
            ->where('status', 'approved')
            ->exists();
    }

    protected function getActions(): array
    {
        return [
            PageAction::make('dashboard')
                ->label('Kembali ke Dasbor')
                ->url(route('filament.admin.pages.2'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    public function form(Form $form): Form
    {
        if (! $this->userHasCompleteBiodata()) {
            return $form
                ->schema([
                    Section::make('⚠️ Biodata Belum Lengkap')
                        ->description('Silakan lengkapi Prodi, NPM, Tahun Angkatan, dan Nilai Basic Listening sebelum mengajukan.')
                        ->schema([
                            FormActions::make([
                                FormAction::make('go_biodata')
                                    ->label('Lengkapi Biodata')
                                    ->url(\App\Filament\Pages\Biodata::getUrl())
                                    ->color('warning')
                                    ->icon('heroicon-o-pencil-square')
                                    ->extraAttributes(['class' => 'mx-auto']),
                            ]),
                        ])
                        ->extraAttributes(['class' => 'flex flex-col items-center justify-center text-center']),
                ])
                ->statePath('data');
        }

        if ($this->hasSubmissions) {
            return $form->schema([])->statePath('data');
        }

        return $form
            ->schema([
                // TES 1
                Section::make('Data Tes 1')
                    ->description('Masukkan data untuk nilai tes pertama Anda.')
                    ->schema([
                        TextInput::make('nilai_tes_1')
                            ->label('Nilai Tes')
                            ->numeric()->required()
                            ->rule('integer')->rule('between:0,677'),
                        DatePicker::make('tanggal_tes_1')
                            ->label('Tanggal Tes')->required()
                            ->native(false)->displayFormat('d/m/Y'),
                        FileUpload::make('foto_path_1')
                            ->label('Screenshot Nilai Tes')->required()
                            ->disk('public')->directory('ept_proofs')
                            ->image()->imageEditor(false)->maxSize(2_048)
                            ->imagePreviewHeight('180'),
                    ])->columns(3),

                // TES 2
                Section::make('Data Tes 2')
                    ->description('Masukkan data untuk nilai tes kedua Anda.')
                    ->schema([
                        TextInput::make('nilai_tes_2')
                            ->label('Nilai Tes')
                            ->numeric()->required()
                            ->rule('integer')->rule('between:0,677'),
                        DatePicker::make('tanggal_tes_2')
                            ->label('Tanggal Tes')->required()
                            ->native(false)->displayFormat('d/m/Y')
                            ->rule('after_or_equal:tanggal_tes_1'),
                        FileUpload::make('foto_path_2')
                            ->label('Screenshot Nilai Tes')->required()
                            ->disk('public')->directory('ept_proofs')
                            ->image()->imageEditor(false)->maxSize(2_048)
                            ->imagePreviewHeight('180'),
                    ])->columns(3),

                // TES 3
                Section::make('Data Tes 3')
                    ->description('Masukkan data untuk nilai tes ketiga Anda.')
                    ->schema([
                        TextInput::make('nilai_tes_3')
                            ->label('Nilai Tes')
                            ->numeric()->required()
                            ->rule('integer')->rule('between:0,677'),
                        DatePicker::make('tanggal_tes_3')
                            ->label('Tanggal Tes')->required()
                            ->native(false)->displayFormat('d/m/Y')
                            ->rule('after_or_equal:tanggal_tes_2'),
                        FileUpload::make('foto_path_3')
                            ->label('Screenshot Nilai Tes')->required()
                            ->disk('public')->directory('ept_proofs')
                            ->image()->imageEditor(false)->maxSize(2_048)
                            ->imagePreviewHeight('180'),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $existing = EptSubmission::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            Notification::make()
                ->title('Anda sudah memiliki pengajuan.')
                ->body('Silakan menunggu proses atau hubungi admin jika perlu perubahan.')
                ->danger()
                ->send();
            return;
        }

        $formData = $this->form->getState();
        $formData['user_id'] = Auth::id();
        $formData['status']  = 'pending';

        EptSubmission::create($formData);

        Notification::make()->title('Data berhasil dikirim!')->success()->send();

        $this->form->fill([]);
        $this->hasSubmissions = true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(EptSubmission::query()->where('user_id', auth()->id()))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tgl Pengajuan')
                    ->dateTime('d/m/Y H:i')
                    ->since()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default    => (string) $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),

                // Kolom tambahan disembunyikan default (bisa ditampilkan via toggle)
                TextColumn::make('nilai_tes_1')->label('Tes I')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nilai_tes_2')->label('Tes II')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nilai_tes_3')->label('Tes III')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('catatan_admin')->label('Catatan Staf')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                // Download sebagai tombol
                \Filament\Tables\Actions\Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (EptSubmission $r) => $r->status === 'approved')
                    ->url(fn (EptSubmission $r) =>
                        filled($r->verification_code)
                            ? route('verification.ept.pdf', ['code' => $r->verification_code])
                            : route('ept-submissions.pdf', $r)
                    )
                    ->openUrlInNewTab()
                    ->button(),

                // Verifikasi sebagai link
                \Filament\Tables\Actions\Action::make('verify')
                    ->label('Lihat Verifikasi')
                    ->icon('heroicon-o-link')
                    ->visible(fn (EptSubmission $r) => $r->status === 'approved' && filled($r->verification_code))
                    ->url(fn (EptSubmission $r) =>
                        $r->verification_url ?: route('verification.show', ['code' => $r->verification_code], true)
                    )
                    ->openUrlInNewTab()
                    ->button(),
            ])
            ->paginated(false);
    }

    protected function getFormActions(): array
    {
        if (! $this->userHasCompleteBiodata() || $this->hasSubmissions) {
            return [];
        }

        return [
            PageAction::make('submit')
                ->label('Ajukan Surat Rekomendasi')
                ->action('submit')
                ->extraAttributes(['class' => 'mx-auto flex justify-center mt-6']),
        ];
    }

    public function getApprovedSubmissionProperty(): ?EptSubmission
    {
        return EptSubmission::where('user_id', Auth::id())
            ->where('status', 'approved')
            ->orderByRaw('COALESCE(approved_at, created_at) DESC')
            ->first();
    }

    public function getLatestSubmissionProperty(): ?EptSubmission
    {
        return EptSubmission::where('user_id', Auth::id())
            ->latest('created_at')
            ->first();
    }

    protected function getHeaderActions(): array
    {
        // tombol "Kembali" selalu ada
        $actions = [
            \Filament\Actions\Action::make('back_to_dashboard')
                ->label('Kembali ke Dasbor')
                ->icon('heroicon-m-arrow-left')
                ->color('gray')
                ->url(route('filament.admin.pages.2')),
        ];

        if ($rec = $this->approvedSubmission) {
            $pdfUrl = filled($rec->verification_code)
                ? route('verification.ept.pdf', ['code' => $rec->verification_code])
                : route('ept-submissions.pdf', $rec);

            $verifyUrl = $rec->verification_url
                ?: (filled($rec->verification_code)
                    ? route('verification.show', ['code' => $rec->verification_code], true)
                    : null);

            if ($verifyUrl) {
                $actions[] = \Filament\Actions\Action::make('verify_header')
                    ->label('Lihat Verifikasi')
                    ->icon('heroicon-m-link')
                    ->color('gray')
                    ->url($verifyUrl)
                    ->openUrlInNewTab();
            }

            $actions[] = \Filament\Actions\Action::make('download_pdf_header')
                ->label('Download PDF')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->url($pdfUrl)
                ->openUrlInNewTab();

        }

        return $actions;
    }
}
