<?php

namespace App\Filament\Pages;

use App\Models\Prody;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class Biodata extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-s-cog-6-tooth';
    protected static ?string $navigationLabel = 'Account Settings';
    protected static string $view = 'filament.pages.biodata';

    public $user;
    public ?array $data = [];

    public function mount(): void
    {
        $this->user = Auth::user();

        $this->form->fill([
            'name' => $this->user->name,
            'email' => $this->user->email,
            'srn' => $this->user->srn,
            'prody_id' => $this->user->prody_id,
            'year' => $this->user->year,
            'nilaibasiclistening' => $this->user->nilaibasiclistening,
            'image' => $this->user->image,
        ]);
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
                        ->helperText(str('Isi dengan **nama lengkap** disini.')->inlineMarkdown()->toHtmlString()),
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
                        ->helperText('Isi dengan angka 0 jika bukan Mahasiswa.'),

                    FileUpload::make('image')->image()
                        ->label('Foto Profil')
                        ->helperText(str('Upload foto profil Anda disini. Ukuran maksimal 2MB.')->inlineMarkdown()->toHtmlString())
                        ->directory('profile_pictures')
                        ->maxSize(2048)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                ])
            ])->statePath('data');
    }

    public function edit(): void
    {
        $validatedData = $this->form->getState();

        $this->user->name = $validatedData['name'];
        $this->user->email = $validatedData['email'];
        $this->user->srn = $validatedData['srn'];
        $this->user->prody_id = $validatedData['prody_id'];
        $this->user->year = $validatedData['year'];
        $this->user->nilaibasiclistening = $validatedData['nilaibasiclistening'];

        if (!empty($validatedData['password'])) {
            $this->user->password = Hash::make($validatedData['password']);
        }

        if (isset($validatedData['image'])) {
            if ($this->user->image) {
                Storage::delete($this->user->image);
            }
            $this->user->image = $validatedData['image'];
        }

        $this->user->save();

        Notification::make()
            ->title('Informasi Terupdate')
            ->success()
            ->body('Informasi akun Anda telah diperbarui.')
            ->send();
    }
}