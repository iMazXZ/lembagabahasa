<?php

namespace App\Observers;

use App\Models\PendaftaranEpt;
use App\Models\User;
use App\Notifications\PendaftarEptNotification;
use Filament\Notifications\Notification;

class PendaftaranEptObserver
{
    /**
     * Handle the PendaftaranEpt "created" event.
     */
    public function created(PendaftaranEpt $pendaftaranEpt): void
    {
        // Notifikasi untuk admin/user tertentu
        $admins = User::role('admin')->get();
        
        foreach ($admins as $admin) {
            Notification::make()
            ->title('Pendaftaran EPT Baru')
            ->body("Pendaftaran EPT baru {$pendaftaranEpt->users->name} - {$pendaftaranEpt->users->srn} | {$pendaftaranEpt->users->prody->name} - {$pendaftaranEpt->users->year}")
            ->icon('heroicon-o-document-plus')
            ->color('success')
            ->sendToDatabase($admin);
        }
    }

    /**
     * Handle the PendaftaranEpt "updated" event.
     */
    public function updated(PendaftaranEpt $pendaftaranEpt): void
    {
        if ($pendaftaranEpt->wasChanged('status_pembayaran')) {
            Notification::make()
                ->title('Status Pembayaran')
                ->body("Status Pendaftaran EPT " . $pendaftaranEpt->created_at->isoFormat('dddd, D/M/Y') . " telah " . ($pendaftaranEpt->status_pembayaran === 'approved' ? 'Diterima' : 'Ditolak'))
                ->icon('heroicon-m-arrow-path')
                ->color('warning')
                ->sendToDatabase($pendaftaranEpt->users);
        }
    }

    /**
     * Handle the PendaftaranEpt "deleted" event.
     */
    public function deleted(PendaftaranEpt $pendaftaranEpt): void
    {
        $admins = User::role('admin')->get();
        
        foreach ($admins as $admin) {
            Notification::make()
                ->title('Pendaftaran Dihapus')
                ->body("Pendaftaran #{$pendaftaranEpt->id} dengan status {$pendaftaranEpt->status_pembayaran} telah dihapus [{$pendaftaranEpt->users->name} - {$pendaftaranEpt->users->srn} - {$pendaftaranEpt->users->prody->name} - {$pendaftaranEpt->users->year}")
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->sendToDatabase($admin);
        }
    }

    /**
     * Handle the PendaftaranEpt "restored" event.
     */
    public function restored(PendaftaranEpt $pendaftaranEpt): void
    {
        //
    }

    /**
     * Handle the PendaftaranEpt "force deleted" event.
     */
    public function forceDeleted(PendaftaranEpt $pendaftaranEpt): void
    {
        //
    }
}
