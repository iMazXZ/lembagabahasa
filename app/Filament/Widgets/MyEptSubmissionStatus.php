<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\SubmitEptScore;
use App\Models\EptSubmission;
use Filament\Actions\Action;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class MyEptSubmissionStatus extends Widget
{
    protected static string $view = 'filament.widgets.my-ept-submission-status';

    protected int | string | array $columnSpan = 'full';
    
    public static function canView(): bool
    {
        $u = auth()->user();
        return $u?->hasRole('pendaftar') ?? false;
    }

    /** submission terakhir milik user */
    public ?Model $submission = null;

    /** user punya riwayat apa tidak */
    public bool $hasSubmissions = false;

    public function mount(): void
    {
        $uid = auth()->id();
        if (! $uid) return;

        $this->submission = EptSubmission::where('user_id', $uid)
            ->latest('created_at')
            ->first();

        $this->hasSubmissions = EptSubmission::where('user_id', $uid)->exists();
    }

    /** URL menuju halaman pengajuan/riwayat */
    public function getListUrl(): string
    {
        return SubmitEptScore::getUrl();
    }

    /** Tombol: Ajukan sekarang (empty state) */
    protected function getAjukanAction(): Action
    {
        return Action::make('ajukan')
            ->label('Ajukan Surat Rekomendasi')
            ->url($this->getListUrl())
            ->color('primary')
            ->icon('heroicon-s-document-plus');
    }

    /** Tombol: Riwayat pengajuan */
    protected function getRiwayatAction(): Action
    {
        return Action::make('riwayat')
            ->label('Riwayat')
            ->url($this->getListUrl())
            ->color('primary')
            ->icon('heroicon-s-document-text');
    }

    /** Tombol: Ajukan ulang (saat ditolak) */
    protected function getAjukanUlangAction(): Action
    {
        return Action::make('ajukanUlang')
            ->label('Ajukan Ulang')
            ->url($this->getListUrl())
            ->color('danger')
            ->icon('heroicon-s-arrow-path');
    }

    /** Tombol: Lihat detail (saat approved) */
    protected function getLihatDetailAction(): Action
    {
        return Action::make('lihatDetail')
            ->label('Lihat Detail')
            ->url($this->getListUrl())
            ->color('success')
            ->icon('heroicon-s-check-badge');
    }
}
