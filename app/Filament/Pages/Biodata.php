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
        return $form->schema([
            Section::make()->schema([
                TextInput::make('name')->required()
                    ->label('Nama Lengkap'),
                TextInput::make('email')->required()->email(),
                TextInput::make('password')
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->nullable(),
                TextInput::make('srn')
                    ->label('NPM / Nomor Pokok Mahasiswa'),

                // Gunakan Select untuk pilih prodi dari tabel `prodies`
                Select::make('prody_id')
                    ->label('Program Studi')
                    ->options(Prody::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('year')
                    ->label('Tahun Angkatan'),
                TextInput::make('nilaibasiclistening')
                    ->label('Masukan Nilai Basic Listening'),
                FileUpload::make('image')->image()->columnSpanFull(),
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
            ->title('Biodata Updated')
            ->success()
            ->body('Your Biodata Has Been Successfully Updated.')
            ->send();
    }
}