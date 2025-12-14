<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    /**
     * Check if OTP is enabled (for mobile app to know which flow to use)
     */
    public function checkOtpStatus()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'otp_enabled' => SiteSetting::isOtpEnabled(),
            ],
        ]);
    }

    /**
     * Send OTP for WhatsApp verification (follows same logic as web)
     */
    public function sendOtp(Request $request, WhatsAppService $waService)
    {
        $request->validate([
            'whatsapp' => ['required', 'string', 'min:10', 'max:15'],
        ]);

        $user = $request->user();
        $normalized = preg_replace('/[^0-9]/', '', $request->input('whatsapp'));
        
        // Pastikan diawali 62
        if (str_starts_with($normalized, '0')) {
            $normalized = '62' . substr($normalized, 1);
        }

        // Cek apakah OTP diaktifkan dari pengaturan situs
        if (SiteSetting::isOtpEnabled()) {
            // OTP AKTIF: Generate dan kirim OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(5);

            $user->update([
                'whatsapp' => $normalized,
                'whatsapp_otp' => $otp,
                'whatsapp_otp_expires_at' => $expiresAt,
                'whatsapp_verified_at' => null,
            ]);

            // Kirim OTP via WhatsApp
            if (!$waService->isEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Layanan WhatsApp tidak tersedia',
                ], 503);
            }

            $sent = $waService->sendOtp($normalized, $otp);

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kode OTP telah dikirim ke WhatsApp Anda',
                    'data' => [
                        'skip_otp' => false,
                        'expires_in' => 300, // 5 minutes in seconds
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim OTP. Pastikan nomor WhatsApp aktif.',
            ], 500);
        }

        // OTP NONAKTIF: Langsung simpan dan verifikasi
        $user->update([
            'whatsapp' => $normalized,
            'whatsapp_otp' => null,
            'whatsapp_otp_expires_at' => null,
            'whatsapp_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil disimpan!',
            'data' => [
                'skip_otp' => true,
                'whatsapp' => $normalized,
                'whatsapp_verified' => true,
            ],
        ]);
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $inputOtp = $request->input('otp');

        // Cek apakah ada OTP yang pending
        if (!$user->whatsapp_otp || !$user->whatsapp_otp_expires_at) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada OTP yang pending. Silakan kirim ulang OTP.',
            ], 400);
        }

        // Cek expired
        if (now()->isAfter($user->whatsapp_otp_expires_at)) {
            $user->update([
                'whatsapp_otp' => null,
                'whatsapp_otp_expires_at' => null,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah kadaluarsa. Silakan kirim ulang.',
            ], 400);
        }

        // Verifikasi OTP
        if ($user->whatsapp_otp !== $inputOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid.',
            ], 400);
        }

        // Sukses - tandai sebagai verified
        $user->update([
            'whatsapp_otp' => null,
            'whatsapp_otp_expires_at' => null,
            'whatsapp_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil diverifikasi!',
            'data' => [
                'whatsapp' => $user->whatsapp,
                'whatsapp_verified' => true,
            ],
        ]);
    }

    /**
     * Delete WhatsApp number
     */
    public function delete(Request $request)
    {
        $user = $request->user();
        
        $user->update([
            'whatsapp' => null,
            'whatsapp_otp' => null,
            'whatsapp_otp_expires_at' => null,
            'whatsapp_verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil dihapus',
        ]);
    }
}
