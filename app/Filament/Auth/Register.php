<?php

namespace App\Filament\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as AuthRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Register extends AuthRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeform()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        TextInput::make('srn')
                            ->label('Nomor Pokok Mahasiswa (NPM)')
                            ->placeholder('Contoh: 21430057')
                            ->numeric()
                            ->minLength(8)
                            ->maxLength(10)
                            ->unique(table: 'users', column: 'srn')
                            ->prefixIcon('heroicon-o-identification')
                            ->columnSpanFull(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            )
        ];
    }

    protected function handleRegistration(array $data): Model
    {
        $user = $this->getUserModel()::create($data);

        // Assign role pendaftar
        $user->assignRole('pendaftar');

        return $user;
    }
}
