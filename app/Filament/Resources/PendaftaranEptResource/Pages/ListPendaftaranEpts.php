<?php

namespace App\Filament\Resources\PendaftaranEptResource\Pages;

use App\Filament\Resources\PendaftaranEptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\StatusPembayaran; // Ensure this is the correct namespace for StatusPembayaran
use Filament\Resources\Components\Tab;


class ListPendaftaranEpts extends ListRecords
{
   
    protected static string $resource = PendaftaranEptResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $isComplete = $user->prody && $user->nilaibasiclistening && $user->srn && $user->year;

        return $isComplete
            ? [Actions\CreateAction::make()]
            : [];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if (Auth::user()->hasRole('Admin')) {
            return $query; // Admin bisa lihat semua
        }

        // Selain admin hanya bisa lihat data sendiri
        return $query->where('user_id', Auth::id());
    }

    public function getTabs(): array
    {
        $user = Auth::user();
        if (!($user->hasRole('Admin') || $user->hasRole('Staf Administrasi'))) {
            return [];
        }

        return [
                        
            'pending' => Tab::make('Menunggu')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_pembayaran', 'pending'))
                ->badge(fn () => $this->getModel()::where('status_pembayaran', 'pending')->count())
                ->badgeColor('warning'),

            'today' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(fn () => $this->getModel()::whereDate('created_at', today())->count())
                ->badgeColor('info'),

            'approved' => Tab::make('Disetujui')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_pembayaran', 'approved'))
                ->badge(fn () => $this->getModel()::where('status_pembayaran', 'approved')->count())
                ->badgeColor('success'),

            'rejected' => Tab::make('Ditolak')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_pembayaran', 'rejected'))
                ->badge(fn () => $this->getModel()::where('status_pembayaran', 'rejected')->count())
                ->badgeColor('danger'),

            'all' => Tab::make('Semua')
                ->badge(fn () => $this->getModel()::count()),
        ];
    }

    public function getSubheading(): ?string
    {
        $user = Auth::user();
        $isComplete = $user->prody && $user->nilaibasiclistening && $user->srn && $user->year;

        if (!$isComplete) {
            return '⚠️ Silakan lengkapi terlebih dahulu data biodata Anda. Pastikan seluruh data telah terisi dengan benar untuk melanjutkan proses pendaftaran.';
        }

        return '';
    }

    protected function canCreate(): bool
    {
        return true; // Or put your own permission logic here
    }
}

