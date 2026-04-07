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
                          ->orWhere('grup_3_id', $groupId)
                          ->orWhere('grup_4_id', $groupId);
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
                        return (string) ($record->testNumberForGroupId((int) $groupId) ?? '-');
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
        
        $tesNum = $registration->testNumberForGroupId((int) $group->id);
        
        try {
            $jadwal = $group->jadwal->translatedFormat('l, d F Y H:i');
            $dashboardUrl = route('dashboard.ept-registration.index');

            $message = "*Jadwal Tes EPT Ditetapkan*\n\n";
            $message .= "Yth. *{$user->name}*,\n\n";
            $message .= "Jadwal *Tes ke-{$tesNum}* EPT Anda telah ditetapkan:\n\n";
            $message .= "*Grup:* {$group->name}\n";
            $message .= "*Waktu:* {$jadwal} WIB\n";
            $message .= "*Lokasi:* {$group->lokasi}\n\n";
            $message .= "Silakan download dan cetak Kartu Peserta melalui:\n{$dashboardUrl}\n\n";
            $message .= "Setelah tes selesai, nilai dan kelulusan tidak dikirim via WA. Silakan cek mandiri di:\nhttps://lembagabahasa.site/nilai-ujian\n\n";
            $message .= "_Wajib print & membawa kartu peserta dan KTP/Kartu Mahasiswa setiap kali tes._";

            $queued = app(WhatsAppService::class)->queueMessage($user->whatsapp, $message);

            if ($queued) {
                Notification::make()
                    ->success()
                    ->title('Notifikasi diantrikan')
                    ->body("Pesan masuk antrean pengiriman untuk {$user->name}")
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title('Gagal mengantrikan')
                    ->body('Layanan WhatsApp tidak aktif')
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
