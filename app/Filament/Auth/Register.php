<?php

namespace App\Filament\Auth;

use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as AuthRegister;
use Illuminate\Database\Eloquent\Model;

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
            ),
        ];
    }

    /**
     * Custom komponen email: tambahkan validasi typo ".con"
     */
    protected function getEmailFormComponent(): TextInput
    {
        // mulai dari versi bawaan AuthRegister (sudah ada ->email(), ->required(), ->unique(), dll)
        return parent::getEmailFormComponent()
            ->rule(function () {
                return function (string $attribute, $value, Closure $fail) {
                    $value = strtolower(trim($value));

                    // Kalau berakhiran .con → kemungkinan typo
                    if (str_ends_with($value, '.con')) {
                        $fail('Sepertinya ada typo di email: gunakan ".com", bukan ".con".');
                    }
                };
            });
    }

    protected function handleRegistration(array $data): Model
    {
        $user = $this->getUserModel()::create($data);

        // default role pendaftar
        $user->assignRole('pendaftar');

        return $user;
    }

    protected function getRedirectUrl(): string
    {
        return route('dashboard');
    }
}
