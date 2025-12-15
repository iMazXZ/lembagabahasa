<?php

namespace App\Filament\Resources\EptGroupResource\Widgets;

use App\Models\EptGroup;
use App\Models\EptRegistration;
use App\Services\WhatsAppService;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class PesertaWidget extends BaseWidget
{
    public ?Model $record = null;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Daftar Peserta';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $groupId = $this->record->id;
                
                return EptRegistration::query()
                    ->where(function ($q) use ($groupId) {
                        $q->where('grup_1_id', $groupId)
                          ->orWhere('grup_2_id', $groupId)
                          ->orWhere('grup_3_id', $groupId);
                    })
                    ->with(['user.prody']);
            })
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.srn')
                    ->label('NIM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.prody.name')
                    ->label('Prodi')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tes_ke')
                    ->label('Tes Ke')
                    ->badge()
                    ->getStateUsing(function (EptRegistration $record) {
                        $groupId = $this->record->id;
                        return match(true) {
                            $record->grup_1_id === $groupId => '1',
                            $record->grup_2_id === $groupId => '2',
                            $record->grup_3_id === $groupId => '3',
                            default => '-',
                        };
                    }),
                Tables\Columns\IconColumn::make('wa_status')
                    ->label('WA')
                    ->boolean()
                    ->getStateUsing(fn (EptRegistration $record) => 
                        $record->user->whatsapp && $record->user->whatsapp_verified_at
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('kirim_wa')
                    ->label('Kirim WA')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->size('sm')
                    ->visible(fn (EptRegistration $record) => 
                        $record->user->whatsapp && 
                        $record->user->whatsapp_verified_at &&
                        $this->record->jadwal
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Notifikasi')
                    ->modalDescription(fn (EptRegistration $record) => 
                        "Kirim notifikasi jadwal ke {$record->user->name}?"
                    )
                    ->action(function (EptRegistration $record) {
                        $this->sendIndividualWA($record);
                    }),
            ])
            ->emptyStateHeading('Belum ada peserta')
            ->emptyStateDescription('Peserta akan muncul setelah pendaftaran disetujui dan dimasukkan ke grup ini.')
            ->paginated([10, 25, 50]);
    }

    protected function sendIndividualWA(EptRegistration $registration): void
    {
        $user = $registration->user;
        $group = $this->record;
        
        $tesNum = match(true) {
            $registration->grup_1_id === $group->id => 1,
            $registration->grup_2_id === $group->id => 2,
            $registration->grup_3_id === $group->id => 3,
            default => null,
        };
        
        try {
            $jadwal = $group->jadwal->translatedFormat('l, d F Y H:i');
            $dashboardUrl = route('dashboard.ept-registration.index');

            $message = "*Jadwal Tes EPT Ditetapkan*\n\n";
            $message .= "Yth. *{$user->name}*,\n\n";
            $message .= "Jadwal *Tes ke-{$tesNum}* EPT Anda telah ditetapkan:\n\n";
            $message .= "*Grup:* {$group->name}\n";
            $message .= "*Waktu:* {$jadwal} WIB\n";
            $message .= "*Lokasi:* {$group->lokasi}\n\n";
            $message .= "Silakan download Kartu Peserta melalui:\n{$dashboardUrl}\n\n";
            $message .= "_Wajib membawa kartu peserta dan KTP/Kartu Mahasiswa._";

            $sent = app(WhatsAppService::class)->sendMessage($user->whatsapp, $message);

            if ($sent) {
                Notification::make()
                    ->success()
                    ->title('Notifikasi terkirim')
                    ->body("Pesan berhasil dikirim ke {$user->name}")
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title('Gagal mengirim')
                    ->body('Cek koneksi WhatsApp API')
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Gagal mengirim')
                ->body($e->getMessage())
                ->send();
        }
    }
}
