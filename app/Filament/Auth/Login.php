<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * Ambil kredensial dari form login.
     * Di sini kita:
     * - Cari user pakai email yang dinormalisasi (lowercase + trim) via WHERE LOWER(email)
     * - Tampilkan pesan khusus untuk kasus .com / .con
     * - Jika cocok, kirim balik email persis seperti yang ada di database ke Auth::attempt()
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $rawEmail      = $data['email'];                 // apa adanya dari form
        $normalized    = strtolower(trim($rawEmail));    // untuk pencarian & analisis

        // 1. Cari user berdasarkan email (case-insensitive)
        $user = User::whereRaw('LOWER(email) = ?', [$normalized])->first();

        // 2. Kalau tidak ketemu, cek kemungkinan typo .com / .con
        if (! $user) {
            // Kalau ketiknya .com, cek apakah di database ada .con
            if (str_ends_with($normalized, '.com')) {
                $altEmailLower = preg_replace('/\.com$/i', '.con', $normalized);

                $altUser = User::whereRaw('LOWER(email) = ?', [$altEmailLower])->first();

                if ($altUser) {
                    throw ValidationException::withMessages([
                        'data.email' => 'Email ini tidak ditemukan. '
                            . 'Saat mendaftar, Anda mungkin mengetik: ' . $altUser->email . '. '
                            . 'Coba gunakan email tersebut atau hubungi admin untuk pembaruan.',
                    ]);
                }
            }

            // Kalau ketiknya .con, cek apakah di database ada .com
            if (str_ends_with($normalized, '.con')) {
                $altEmailLower = preg_replace('/\.con$/i', '.com', $normalized);

                $altUser = User::whereRaw('LOWER(email) = ?', [$altEmailLower])->first();

                if ($altUser) {
                    throw ValidationException::withMessages([
                        'data.email' => 'Email ini tidak ditemukan. '
                            . 'Mungkin maksud Anda: ' . $altUser->email . '.',
                    ]);
                }
            }

            // Kalau sama sekali tidak ada user
            throw ValidationException::withMessages([
                'data.email' => 'Email ini belum terdaftar di sistem.',
            ]);
        }

        // 3. Kalau user ketemu, cek password manual
        if (! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'data.password' => 'Kata sandi yang Anda masukkan salah. Klik Lupa Kata Sandi atau Hubungi Admin.',
            ]);
        }

        // 4. Kembalikan kredensial untuk Auth::attempt()
        //    PENTING: pakai email persis seperti di database, bukan $normalized
        return [
            'email'    => $user->email,
            'password' => $data['password'],
        ];
    }

    /**
     * Setelah login sukses, arahkan ke /dashboard (Blade, bukan panel admin langsung)
     */
    protected function getRedirectUrl(): string
    {
        return route('dashboard');
    }
}
