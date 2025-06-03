<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\PendaftaranEpt;
use App\Models\Penerjemahan;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\Auth;

class StatsWidget extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi', 'Kepala Lembaga']);
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total User Pendaftar', User::whereHas('roles', function ($query) {
                    $query->where('name', 'pendaftar');
                })->count())
                ->description('User Pendaftar')
                ->descriptionIcon('heroicon-m-user-group', IconPosition::Before)
                ->chart([1, 3, 5, 10, 20, 40])
                ->color('success'),
            Stat::make('Pendaftaran EPT', PendaftaranEpt::where('status_pembayaran', 'pending')->count())
                ->description('Pendaftaran EPT yang perlu ditinjau')
                ->descriptionIcon('heroicon-m-document-text', IconPosition::Before)
                ->chart([2, 4, 6, 8, 10, 12])
                ->color('warning'),
            Stat::make('Penerjemahan Dokumen Abstrak', Penerjemahan::where('status', 'Menunggu')->count())
                ->description('Permohonan yang belum ditinjau')
                ->descriptionIcon('heroicon-m-document-arrow-down', IconPosition::Before)
                ->chart([1, 2, 3, 4, 5, 6])
                ->color('danger'),
        ];
    }
}
