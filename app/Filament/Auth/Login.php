<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected function getCredentialsFromFormData(array $data): array
    {
        $email = strtolower(trim($data['email']));

        // 1. Coba cari user berdasarkan email yang diketik
        $user = User::where('email', $email)->first();

        // 2. Kalau tidak ketemu, coba cek kemungkinan typo .com / .con
        if (! $user) {
            // Kalau ketiknya .com, cek apakah di database ada .con
            if (str_ends_with($email, '.com')) {
                $altEmail = preg_replace('/\.com$/i', '.con', $email);
                $altUser  = User::where('email', $altEmail)->first();

                if ($altUser) {
                    throw ValidationException::withMessages([
                        'data.email' => 'Email ini tidak ditemukan. '
                            . 'Saat mendaftar, Anda mungkin mengetik: ' . $altEmail . '. '
                            . 'Coba gunakan email tersebut atau hubungi admin untuk pembaruan.',
                    ]);
                }
            }

            // Kalau ketiknya .con, cek apakah di database ada .com
            if (str_ends_with($email, '.con')) {
                $altEmail = preg_replace('/\.con$/i', '.com', $email);
                $altUser  = User::where('email', $altEmail)->first();

                if ($altUser) {
                    throw ValidationException::withMessages([
                        'data.email' => 'Email ini tidak ditemukan. '
                            . 'Mungkin maksud Anda: ' . $altEmail . '.',
                    ]);
                }
            }

            // Kalau sama sekali tidak ada user
            throw ValidationException::withMessages([
                'data.email' => 'Email ini belum terdaftar di sistem.',
            ]);
        }

        // 3. Kalau user ketemu, cek password
        if (! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'data.password' => 'Kata sandi yang Anda masukkan salah. Klik Lupa Kata Sandi atau Hubungi Admin.',
            ]);
        }

        // 4. Kembalikan kredensial standar
        return [
            'email'    => $email,
            'password' => $data['password'],
        ];
    }
}
