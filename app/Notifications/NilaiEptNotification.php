<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\PendaftaranGrupTes;

class NilaiEptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $record;
    public $tanggal;

    public function __construct($record, $tanggal)
    {
        $this->record = $record;
        $this->tanggal = $tanggal;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $grup = $this->record->masterGrupTes;

        $urutan = PendaftaranGrupTes::whereHas('pendaftaranEpt', function ($query) use ($notifiable) {
                $query->whereHas('users', function ($q) use ($notifiable) {
                    $q->where('id', $notifiable->id);
                });
            })
            ->orderBy('id')
            ->pluck('id')
            ->search($this->record->id) + 1;

        $users = $notifiable;
        $userProdi = $users->prody->name ?? '-';

        return (new MailMessage)
            ->subject('Hasil Tes EPT ke-' . $urutan . ' [' . $users->name . ']')
            ->greeting('Halo ' . $users->name . ',')
            ->line('**' . $users->srn . '** ' . 'Prodi **' . $userProdi . '**')
            ->line('Berikut **Hasil Tes EPT ke-' . $urutan . '** Pada **' . \Carbon\Carbon::parse($this->tanggal)->translatedFormat('d F Y') . '**')
            ->line('Listening Comprehension **[' . $this->record->listening_comprehension . ']**')
            ->line('Structure & Written Expression **[' . $this->record->structure_written_expr . ']**')
            ->line('Reading Comprehension **[' . $this->record->reading_comprehension . ']**')
            ->line('Total Score **' . $this->record->total_score . '** | ' . ($this->record->rank === 'Fail' ? 'Maaf Anda **Gagal** Karena **Tidak Mencapai Nilai Minimum**' : 'Selamat Anda Telah **Lulus**'))
            ->salutation('*Terima kasih telah mengikuti tes.*');
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
