<?php

namespace App\Filament\Resources\EptGroupResource\Widgets;

use App\Models\EptGroup;
use App\Models\EptRegistration;
use App\Notifications\EptScheduleAssignedNotification;
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
                    ->label('Kirim Notifikasi')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->size('sm')
                    ->visible(fn (EptRegistration $record) => $this->record->jadwal && $record->user !== null)
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
            if ($tesNum === null) {
                Notification::make()
                    ->danger()
                    ->title('Gagal memproses notifikasi')
                    ->body('Peserta tidak terdaftar pada grup ini.')
                    ->send();
                return;
            }

            $user->notify(new EptScheduleAssignedNotification(
                testNumber: $tesNum,
                groupName: $group->name,
                scheduledAt: $group->jadwal,
                location: (string) $group->lokasi,
                dashboardUrl: route('dashboard.ept-registration.index'),
            ));

            $body = $user->whatsapp && $user->whatsapp_verified_at
                ? "Email diproses dan WA masuk antrean untuk {$user->name}"
                : "Email diproses untuk {$user->name}. WA dilewati karena nomor belum terverifikasi";

            Notification::make()
                ->success()
                ->title('Notifikasi diproses')
                ->body($body)
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->danger()
                ->title('Gagal memproses notifikasi')
                ->body($e->getMessage())
                ->send();
        }
    }
}
