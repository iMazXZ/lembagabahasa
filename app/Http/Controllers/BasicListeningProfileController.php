<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BasicListeningProfileController extends Controller
{
    public function updateGroupNumber(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nomor_grup_bl' => ['required','integer','between:1,2'],
        ], [
            'nomor_grup_bl.required' => 'Nomor grup wajib diisi.',
            'nomor_grup_bl.integer'  => 'Nomor grup harus berupa angka.',
            'nomor_grup_bl.between'  => 'Nomor grup harus antara 1 sampai 3.',
        ]);

        $user->update([
            'nomor_grup_bl' => $validated['nomor_grup_bl'],
        ]);

        return back()->with('success', 'Nomor grup berhasil disimpan.');
    }
}
