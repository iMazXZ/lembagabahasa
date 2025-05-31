<?php

namespace App\Filament\Resources\PendaftaranEptResource\Pages;

use App\Filament\Resources\PendaftaranEptResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Filament\Notifications\Notification;

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
            Notification::make()
                ->title('Lengkapi Biodata Anda')
                ->warning()
                ->body('Anda harus melengkapi biodata terlebih dahulu sebelum melakukan pendaftaran EPT.')
                ->send();

            Redirect::route('filament.pages.biodata')->send();
        }
    }
}