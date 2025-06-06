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
        
        $user->assignRole('pendaftar');

        return $user;
    }
}
