<?php

namespace App\Filament\Resources\PendaftaranEptResource\Pages;

use App\Filament\Resources\PendaftaranEptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


class ListPendaftaranEpts extends ListRecords
{
    // public Collection $orderByStatuses;

    // public function __construct()
    // {
    //     $this->orderByStatuses = Order::select('status', \DB::raw('count(*) as order_count'))
    //         ->groupBy('status')
    //         ->pluck('order_count', 'status');
    // }
    
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

    // public function getTabs(): array
    // {
    //     return [
    //         'pending' => Tab::make('Pending')
    //             ->modifyQueryUsing(function ($query) {
    //                 return $query->where('status', StatusPembayaran::PENDING->value);
    //             }),
    //     ];
    // }

    protected function canCreate(): bool
    {
        return true; // Or put your own permission logic here
    }
}

