<?php

namespace App\Filament\Resources\PenerjemahanResource\Pages;

use App\Filament\Resources\PenerjemahanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\StatusPembayaran;
use Filament\Actions\Action;

class ListPenerjemahans extends ListRecords
{
    protected static string $resource = PenerjemahanResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $isComplete = $user->prody && $user->nilaibasiclistening && $user->srn && $user->year;
        
        $baseActions = [
            Action::make('dashboard')
                ->label('Kembali ke Dasbor')
                ->url(route('filament.admin.home'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
        
        $conditionalActions = [];
        
        if (!$user?->hasRole('penerjemah') && $isComplete) {
            $conditionalActions[] = Actions\CreateAction::make()->label('Permintaan Baru');
        }
        
        return array_merge($baseActions, $conditionalActions);
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Filter hanya data yang ditugaskan ke penerjemah ini
        if (auth()->user()->hasRole('Penerjemah')) {
            return $query->where('translator_id', auth()->id());
        }

        return $query;
    }

    public function getSubheading(): ?string
    {
        $user = Auth::user();
        
        if ($user->hasRole('pendaftar')) {
            $isComplete = $user->prody && $user->nilaibasiclistening && $user->srn && $user->year;

            if (!$isComplete) {
                return '⚠️ Silakan lengkapi terlebih dahulu data biodata Anda. Pastikan seluruh data telah terisi dengan benar untuk melanjutkan proses pendaftaran.';
            }
        }

        return '';
    }
}