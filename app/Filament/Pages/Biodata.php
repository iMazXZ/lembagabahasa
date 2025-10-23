<?php

namespace App\Filament\Pages;

use App\Models\Prody;
use App\Support\ImageTransformer;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class Biodata extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-s-cog-6-tooth';
    protected static ?string $navigationLabel = 'Biodata';
    protected static string $view = 'filament.pages.biodata';

    public $user;
    public ?array $data = [];

    public function mount(): void
    {
        $this->user = Auth::user();

        $this->form->fill([
            'name'                => $this->user->name,
            'email'               => $this->user->email,
            'srn'                 => $this->user->srn,
            'prody_id'            => $this->user->prody_id,
            'year'                => $this->user->year,
            'nilaibasiclistening' => $this->user->nilaibasiclistening,
            'image'               => $this->user->image, // path relatif di disk 'public'
        ]);
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')
                        ->required()
                        ->label('Nama Lengkap')
                        ->validationMessages([
                            'required' => 'Wajib Diisi.',
                        ])
                        ->helperText(str('Isi dengan **nama lengkap** disini.')->inlineMarkdown()->toHtmlString())
                        ->dehydrateStateUsing(fn (string $state): string => ucwords(strtolower($state))),

                    TextInput::make('email')
                        ->required()
                        ->email()
                        ->validationMessages([
                            'required' => 'Wajib Diisi.',
                        ])
                        ->helperText(str('Isi dengan **email** aktif, email ini digunakan untuk mengirim **notifikasi** dan **reset password**.')->inlineMarkdown()->toHtmlString()),

                    TextInput::make('password')
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->nullable()
                        ->hint('Lupa Password? Ganti Disini')
                        ->hintColor('danger'),

                    TextInput::make('srn')
                        ->label('Nomor Pokok Mahasiswa')
                        ->helperText(str('Jika Anda **Mahasiswa** isi NPM disini, jika **Dosen** isi NIDN, jika **Umum** isi dengan NIK.')->inlineMarkdown()->toHtmlString()),

                    Select::make('prody_id')
                        ->label('Program Studi')
                        ->options(Prody::pluck('name', 'id'))
                        ->searchable()
                        ->helperText(str('Pilih **Dosen** atau **Umum** jika bukan Mahasiswa.')->inlineMarkdown()->toHtmlString()),

                    TextInput::make('year')
                        ->label('Tahun Angkatan')
                        ->helperText('Isi dengan Tahun Sekarang jika bukan Mahasiswa.'),

                    TextInput::make('nilaibasiclistening')
                        ->label('Masukan Nilai Basic Listening')
                        ->helperText('Isi dengan angka 0 jika belum/tidak mempunyai nilai.')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100),

                    FileUpload::make('image')
                        ->label('Foto Profil')
                        ->image()
                        ->imageEditor() // user bisa crop manual jika mau
                        ->imageEditorAspectRatios(['1:1'])
                        ->imagePreviewHeight('200')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(8192)
                        ->disk('public')
                        ->visibility('public')
                        ->downloadable()
                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, callable $get) {
                            // Ambil state lama; bisa string atau array
                            $old = $get('image');
                            if (is_array($old)) {
                                $old = $old['path'] ?? ($old[0]['path'] ?? null);
                            }

                            // Hapus file lama bila ada
                            if (is_string($old) && $old !== '' && Storage::disk('public')->exists($old)) {
                                Storage::disk('public')->delete($old);
                            }

                            // Nama file konsisten per user (overwrite)
                            $base = 'avatar_' . str(Auth::id())->padLeft(6, '0') . '.webp';

                            // Kompres ke WebP + resize (tanpa cropSquare)
                            $result = ImageTransformer::toWebpFromUploaded(
                                uploaded:   $file,
                                targetDisk: 'public',
                                targetDir:  'profile_pictures',
                                quality:    82,
                                maxWidth:   600,
                                maxHeight:  600,
                                basename:   $base
                            );

                            return $result['path']; // path relatif pada disk 'public'
                        })
                        ->deleteUploadedFileUsing(function (string $file) {
                            if (Storage::disk('public')->exists($file)) {
                                Storage::disk('public')->delete($file);
                            }
                        })
                        ->helperText('PNG/JPG/WebP ≤ 8MB'),
                ]),
            ])
            ->statePath('data');
    }

    public function edit(): void
    {
        $validated = $this->form->getState();

        $this->user->name                = $validated['name'];
        $this->user->email               = $validated['email'];
        $this->user->srn                 = $validated['srn'];
        $this->user->prody_id            = $validated['prody_id'];
        $this->user->year                = $validated['year'];
        $this->user->nilaibasiclistening = $validated['nilaibasiclistening'];

        if (!empty($validated['password'])) {
            $this->user->password = Hash::make($validated['password']);
        }

        // Normalisasi nilai 'image' (string / array) sebelum simpan
        if (array_key_exists('image', $validated)) {
            $newImage = $validated['image'];
            if (is_array($newImage)) {
                $newImage = $newImage['path'] ?? ($newImage[0]['path'] ?? null);
            }

            // Jika ada perbedaan, hapus file lama untuk berjaga-jaga
            if ($this->user->image && $this->user->image !== $newImage) {
                if (Storage::disk('public')->exists($this->user->image)) {
                    Storage::disk('public')->delete($this->user->image);
                }
            }

            if (is_string($newImage) && $newImage !== '') {
                $this->user->image = $newImage;
            }
        }

        $this->user->save();

        Notification::make()
            ->title('Informasi Terupdate')
            ->success()
            ->body('Informasi akun Anda telah diperbarui.')
            ->send();
    }

    public function getSubheading(): ?string
    {
        $user = Auth::user();

        if ($user->hasRole('pendaftar')) {
            $isComplete =
                !is_null($user->nilaibasiclistening) &&
                ($user->prody !== null && $user->prody !== '') &&
                ($user->srn !== null && $user->srn !== '') &&
                ($user->year !== null && $user->year !== '');

            if (!$isComplete) {
                return '⚠️ Silakan lengkapi terlebih dahulu data biodata Anda. Pastikan seluruh data telah terisi dengan benar untuk bisa melakukan proses pendaftaran.';
            }
        }

        return '';
    }
}
