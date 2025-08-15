<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\SubmitEptScore;
use App\Models\EptSubmission;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class MyEptSubmissionStatus extends Widget
{
    protected static string $view = 'filament.widgets.my-ept-submission-status';

    // Menyembunyikan widget jika user bukan pendaftar
    public static function canView(): bool
    {
        return auth()->user()->hasRole('pendaftar');
    }

    // Variabel untuk menampung data submission
    public ?Model $submission;

    public function mount(): void
    {
        // Ambil data submission TERAKHIR milik user yang sedang login
        $this->submission = EptSubmission::where('user_id', auth()->id())
            ->latest()
            ->first();
    }
}