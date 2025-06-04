<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\PendaftaranGrupTes;

class JadwalTesNotification extends Notification
{
    use Queueable;

    public $record;

    public function __construct(PendaftaranGrupTes $record)
    {
        $this->record = $record;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $grup = $this->record->masterGrupTes;

        // Ambil semua pendaftaran grup milik user ini, urut berdasarkan ID (atau tanggal tes kalau kamu mau)
        $urutan = \App\Models\PendaftaranGrupTes::whereHas('pendaftaranEpt', function ($query) use ($notifiable) {
                $query->whereHas('users', function ($q) use ($notifiable) {
                    $q->where('id', $notifiable->id);
                });
            })
            ->orderBy('id') // Bisa diganti 'grupTes.tanggal_tes' kalau mau berdasarkan tanggal
            ->pluck('id')
            ->search($this->record->id) + 1;

                    
        return (new MailMessage)
            ->subject('Jadwal Tes EPT ke-' . $urutan . ' [' . $notifiable->name . ']')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Informasi **Jadwal Tes EPT ke-' . $urutan . '**')
            ->line('Nomor Grup Tes: **' . $grup->group_number . '**')
            ->line('Pada: **' . \Illuminate\Support\Carbon::parse($grup->tanggal_tes)->format('d/m/Y') . '** Pukul **' . \Illuminate\Support\Carbon::parse($grup->tanggal_tes)->format('H:i') . ' WIB**')
            ->line('Di Ruangan: **' . $grup->ruangan_tes . '** Lantai 2, Kampus 3, UM Metro')
            ->line('Silakan datang tepat waktu. Terima kasih.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
