<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Prody;

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
            'prody_id' => ['required', Rule::exists('prodies','id')],
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
                'max:' . (int) now()->year,
            ],
        ], [
            'prody_id.required' => 'Pilih Program Studi.',
            'prody_id.exists'   => 'Program Studi tidak valid.',
            'srn.required'      => 'NPM wajib diisi.',
            'srn.unique'        => 'NPM ini sudah terdaftar pada pengguna lain. Silakan periksa kembali.',
            'year.required'     => 'Tahun angkatan wajib diisi.',
        ]);

        $user->forceFill($data)->save();

        return redirect($next)->with('success', 'Biodata berhasil dilengkapi. Silakan lanjut.');
    }
}
