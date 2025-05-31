<?php

namespace App\Filament\Resources\PendaftaranGrupTesResource\Pages;

use App\Filament\Resources\PendaftaranGrupTesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\PendaftaranGrupTes;
use App\Models\PendaftaranEpt;

class CreatePendaftaranGrupTes extends CreateRecord
{
    protected static string $resource = PendaftaranGrupTesResource::class;
    
    protected function beforeCreate(): void
    {
        $pendaftaranEptId = $this->data['pendaftaran_ept_id'];
        $grupTesId = $this->data['grup_tes_id'];

        $peserta = PendaftaranEpt::with('users')->find($pendaftaranEptId);

        // Cek jika peserta sudah masuk grup ini
        $sudahMasuk = PendaftaranGrupTes::where('pendaftaran_ept_id', $pendaftaranEptId)
            ->where('grup_tes_id', $grupTesId)
            ->exists();

        if ($sudahMasuk) {
            Notification::make()
                ->title("Peserta {$peserta->users->name} sudah masuk ke grup ini.")
                ->danger()
                ->send();

            $this->halt();
        }

        // Cek batas maksimal 3 grup
        $jumlahGrup = PendaftaranGrupTes::where('pendaftaran_ept_id', $pendaftaranEptId)->count();

        if ($jumlahGrup >= 3) {
            Notification::make()
                ->title("Peserta {$peserta->users->name} sudah mencapai batas 3 grup.")
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
