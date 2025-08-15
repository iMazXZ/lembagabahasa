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

// Tambahkan implementasi HasTable dan InteractsWithTable
class SubmitEptScore extends Page implements HasForms, HasTable
{
    // Tambahkan trait untuk form dan table
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static string $view = 'filament.pages.submit-ept-score';
    protected static ?string $navigationLabel = 'Pengajuan Surat Rekomendasi'; // Ganti label menu
    protected static ?string $title = ' ';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('pendaftar');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('pendaftar');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Tes 1')
                    ->description('Masukkan data untuk nilai tes pertama Anda.')
                    ->schema([
                        TextInput::make('nilai_tes_1')->label('Nilai Tes')->numeric()->required(),
                        DatePicker::make('tanggal_tes_1')->label('Tanggal Tes')->required(),
                        FileUpload::make('foto_path_1')
                            ->label('Upload Bukti Tes')
                            ->disk('public')->directory('ept_proofs')->image()->required(),
                    ])->columns(3), // Tampilkan dalam 3 kolom

                Section::make('Data Tes 2')
                    ->description('Masukkan data untuk nilai tes kedua Anda.')
                    ->schema([
                        TextInput::make('nilai_tes_2')->label('Nilai Tes')->numeric()->required(),
                        DatePicker::make('tanggal_tes_2')->label('Tanggal Tes')->required(),
                        FileUpload::make('foto_path_2')
                            ->label('Upload Bukti Tes')
                            ->disk('public')->directory('ept_proofs')->image()->required(),
                    ])->columns(3),

                Section::make('Data Tes 3')
                    ->description('Masukkan data untuk nilai tes ketiga Anda.')
                    ->schema([
                        TextInput::make('nilai_tes_3')->label('Nilai Tes')->numeric()->required(),
                        DatePicker::make('tanggal_tes_3')->label('Tanggal Tes')->required(),
                        FileUpload::make('foto_path_3')
                            ->label('Upload Bukti Tes')
                            ->disk('public')->directory('ept_proofs')->image()->required(),
                    ])->columns(3),
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
                TextColumn::make('catatan_admin')->label('Catatan dari Staf'),
            ])
            ->paginated(false); // Matikan paginasi agar semua riwayat tampil
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Ajukan Surat Rekomendasi')
                ->action('submit'), // <-- INI SOLUSINYA
        ];
    }
}