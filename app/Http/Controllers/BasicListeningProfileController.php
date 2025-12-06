<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Prody;
use Illuminate\Support\Facades\Storage;
use App\Support\ImageTransformer;
use Illuminate\Support\Str;

class BasicListeningProfileController extends Controller
{
    public function updateGroupNumber(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nomor_grup_bl' => ['required','integer','between:1,4'],
        ], [
            'nomor_grup_bl.required' => 'Nomor grup wajib diisi.',
            'nomor_grup_bl.integer'  => 'Nomor grup harus berupa angka.',
            'nomor_grup_bl.between'  => 'Nomor grup harus antara 1 sampai 4.',
        ]);

        $user->update([
            'nomor_grup_bl' => $validated['nomor_grup_bl'],
        ]);

        return back()->with('success', 'Nomor grup berhasil disimpan.');
    }

    public function showCompleteForm(Request $request)
    {
        $user = $request->user();
        $next = $request->query('next', route('bl.index'));

        $prodis = Prody::query()->orderBy('name')->get(['id','name']);

        return view('bl.complete_profile', [
            'user'   => $user,
            'next'   => $next,
            'prodis' => $prodis,
        ]);
    }

    public function submitCompleteForm(Request $request)
    {
        $user = $request->user();
        $next = $request->input('next', route('bl.index'));

        $data = $request->validate([
            // ===== Field akun (opsional, dipakai di dashboard biodata) =====
            'name'  => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            // ===== Field biodata inti =====
            'prody_id' => ['required', Rule::exists('prodies', 'id')],
            'srn'      => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'srn')->ignore($user->id),
            ],
            'year'     => [
                'required',
                'integer',
                'min:2015',
                'max:' . (int) now()->year + 1, // biar sama2 aman
            ],

            // ===== Nilai Basic Listening (conditional) =====
            'nilaibasiclistening' => [
                'nullable',
                Rule::requiredIf(fn () => (int) $request->input('year') <= 2024),
                'numeric',
                'min:0',
                'max:100',
            ],

            // ===== Foto Profil (dikompres) =====
            'image'    => ['nullable', 'mimes:jpeg,jpg,png,webp', 'max:8192'], // 8MB
        ], [
            'prody_id.required' => 'Pilih Program Studi.',
            'prody_id.exists'   => 'Program Studi tidak valid.',

            'srn.required'      => 'NPM wajib diisi.',
            'srn.unique'        => 'NPM ini sudah terdaftar. Hubungi Admin jika ini memang NPM Anda.',

            'year.required'     => 'Tahun angkatan wajib diisi.',

            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email ini sudah digunakan oleh pengguna lain.',

            'nilaibasiclistening.required' => 'Nilai Basic Listening wajib diisi untuk angkatan 2024 ke bawah.',
            'nilaibasiclistening.numeric'  => 'Nilai Basic Listening harus berupa angka.',
            'nilaibasiclistening.min'      => 'Nilai Basic Listening minimal 0.',
            'nilaibasiclistening.max'      => 'Nilai Basic Listening maksimal 100.',

            'image.mimes'       => 'Foto harus berupa JPG, PNG, atau WebP.',
            'image.max'         => 'Ukuran foto maksimal 8 MB.',
        ]);

        // Normalisasi nama ke uppercase jika diisi
        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $data['name'] = mb_strtoupper(trim($data['name']), 'UTF-8');
        }

        // === FOTO PROFIL: kompres pakai ImageTransformer (union UploadedFile / TemporaryUploadedFile) ===
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // hapus foto lama kalau ada
            $old = $user->image;
            if (is_string($old) && $old !== '' && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            // nama file sama konsepnya dengan Filament: avatar_000123.webp
            $base = 'avatar_' . Str::of($user->id)->padLeft(6, '0') . '.webp';

            $result = ImageTransformer::toWebpFromUploaded(
                uploaded:   $file,
                targetDisk: 'public',
                targetDir:  'profile_pictures',
                quality:    82,
                maxWidth:   600,
                maxHeight:  600,
                basename:   $base
            );

            $data['image'] = $result['path'];
        }

        $user->forceFill($data)->save();

        return redirect($next)->with('success', 'Biodata berhasil diperbarui.');
    }

    public function deletePhoto(Request $request)
    {
        $user = $request->user();

        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

        $user->image = null;
        $user->save();

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }

    public function showDashboardBiodata(Request $request)
    {
        $user   = $request->user();
        $prodis = Prody::query()->orderBy('name')->get(['id', 'name']);

        // bisa pakai input number biasa, jadi tidak wajib kirim list years
        return view('dashboard.biodata', [
            'user'   => $user,
            'prodis' => $prodis,
        ]);
    }
}
