<?php

namespace App\Filament\Resources\PendaftaranEptResource\Pages;

use App\Filament\Resources\PendaftaranEptResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Notifications\PendaftaranBaruNotification;
use App\Models\User;

class CreatePendaftaranEpt extends CreateRecord
{
    protected static string $resource = PendaftaranEptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function mount(): void
    {
        parent::mount();

        $user = Auth::user();

        $isComplete = $user->prody && $user->nilaibasiclistening && $user->srn && $user->year;

        if (!$isComplete) {
            session()->flash('error', 'Anda harus melengkapi biodata terlebih dahulu sebelum melakukan pendaftaran EPT.');
            
            $this->redirect(route('filament.pages.biodata'));
            return;
        }
    }
}