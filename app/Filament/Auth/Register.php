<?php

namespace App\Filament\Auth;

use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as AuthRegister;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;
use Filament\Events\Auth\Registered;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Notifications\Notification;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;

class Register extends AuthRegister
{
    public function register(): ?RegistrationResponseContract
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function () {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        event(new Registered($user));

        $this->sendEmailVerificationNotification($user);

        Filament::auth()->login($user);

        session()->regenerate();

        return new class implements RegistrationResponseContract {
            public function toResponse($request)
            {
                return redirect()->intended(route('dashboard.pendaftar'));
            }
        };
    }

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
        // Normalisasi nama ke UPPERCASE
        if (!empty($data['name'])) {
            $data['name'] = mb_strtoupper(trim($data['name']), 'UTF-8');
        }

        $user = $this->getUserModel()::create($data);

        // default role pendaftar
        $user->assignRole('pendaftar');

        return $user;
    }

    protected function getRedirectUrl(): string
    {
        return route('dashboard.pendaftar');
    }
}
