<?php

namespace App\Filament\Pages;

use App\Models\EptSubmission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use App\Filament\Pages\Biodata;

// Tambahkan implementasi HasTable dan InteractsWithTable
class SubmitEptScore extends Page implements HasForms, HasTable
{
    // Tambahkan trait untuk form dan table
    use InteractsWithForms;
    use InteractsWithTable;

    public bool $hasSubmissions = false;
    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static string $view = 'filament.pages.submit-ept-score';
    protected static ?string $navigationLabel = 'Pengajuan Surat Rekomendasi'; // Ganti label menu
    protected static ?string $title = ' ';

    protected function userHasCompleteBiodata(): bool
    {
        $u = Auth::user();
        return $u
            && !is_null($u->nilaibasiclistening)   // 0 tetap valid
            && $u->prody !== null && $u->prody !== ''
            && $u->srn   !== null && $u->srn   !== ''
            && $u->year  !== null && $u->year  !== '';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();

        $this->hasSubmissions = EptSubmission::where('user_id', Auth::id())->exists();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('pendaftar');
    }

    protected function getActions(): array
    {
        return [
            Action::make('dashboard')
                ->label('Kembali ke Dasbor')
                ->url(route('filament.admin.pages.2'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }
    
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('pendaftar');
    }

    public function form(Form $form): Form
    {
        if (! $this->userHasCompleteBiodata()) {
            // Tidak menampilkan input apa pun
            return $form
                ->schema([
                    Section::make('⚠️ Biodata Belum Lengkap')
                        ->description('Silakan lengkapi Prodi, NPM, Tahun Angkatan, dan Nilai Basic Listening sebelum mengajukan.')
                        ->schema([
                            \Filament\Forms\Components\Actions::make([
                                \Filament\Forms\Components\Actions\Action::make('go_biodata')
                                    ->label('Lengkapi Biodata')
                                    ->url(Biodata::getUrl()) // arahkan ke halaman Biodata
                                    ->color('warning')
                                    ->icon('heroicon-o-pencil-square')
                                    ->extraAttributes(['class' => 'mx-auto']),
                            ]),
                        ])
                        ->extraAttributes(['class' => 'flex flex-col items-center justify-center text-center']),
                ])
                ->statePath('data');
        }

        // --- Biodata lengkap: tampilkan form seperti biasa ---
        return $form
            ->schema([
                Section::make('Data Tes 1')
                    ->description('Masukkan data untuk nilai tes pertama Anda.')
                    ->schema([
                        TextInput::make('nilai_tes_1')->label('Nilai Tes')->numeric()->required(),
                        DatePicker::make('tanggal_tes_1')->label('Tanggal Tes')->required(),
                        FileUpload::make('foto_path_1')->label('Screenshot Nilai Tes')
                            ->disk('public')->directory('ept_proofs')->image()->required(),
                    ])->columns(3)
                    ->extraAttributes(['class' => 'items-center justify-center text-center']),

                Section::make('Data Tes 2')
                    ->description('Masukkan data untuk nilai tes kedua Anda.')
                    ->schema([
                        TextInput::make('nilai_tes_2')->label('Nilai Tes')->numeric()->required(),
                        DatePicker::make('tanggal_tes_2')->label('Tanggal Tes')->required(),
                        FileUpload::make('foto_path_2')->label('Screenshot Nilai Tes')
                            ->disk('public')->directory('ept_proofs')->image()->required(),
                    ])->columns(3)
                    ->extraAttributes(['class' => 'items-center justify-center text-center']),

                Section::make('Data Tes 3')
                    ->description('Masukkan data untuk nilai tes ketiga Anda.')
                    ->schema([
                        TextInput::make('nilai_tes_3')->label('Nilai Tes')->numeric()->required(),
                        DatePicker::make('tanggal_tes_3')->label('Tanggal Tes')->required(),
                        FileUpload::make('foto_path_3')->label('Screenshot Nilai Tes')
                            ->disk('public')->directory('ept_proofs')->image()->required(),
                    ])->columns(3)
                    ->extraAttributes(['class' => 'items-center justify-center text-center']),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $formData = $this->form->getState();
        $formData['user_id'] = Auth::id();
        $formData['status'] = 'pending';

        EptSubmission::create($formData);

        Notification::make()->title('Data berhasil dikirim!')->success()->send();
        $this->form->fill(); // Kosongkan form setelah berhasil
    }

    // METHOD BARU UNTUK MEMBUAT TABEL RIWAYAT
    public function table(Table $table): Table
    {
        return $table
            ->query(EptSubmission::query()->where('user_id', auth()->id()))
            ->columns([
                TextColumn::make('created_at')->label('Tgl Pengajuan')->date()->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger',
                    }),
                TextColumn::make('catatan_admin')
                    ->label('Catatan dari Staf')
                    ->wrap(),
            ])
            ->paginated(false); // Matikan paginasi agar semua riwayat tampil
    }

    protected function getFormActions(): array
    {
        if (! $this->userHasCompleteBiodata()) {
            return []; // tidak ada tombol submit
        }

        return [
            Action::make('submit')
                ->label('Ajukan Surat Rekomendasi')
                ->action('submit')
                ->extraAttributes(['class' => 'mx-auto flex justify-center mt-6']), // tambahkan margin top
        ];
    }

}