<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EptGroupResource\Pages;
use App\Models\EptGroup;
use App\Models\EptRegistration;
use App\Services\WhatsAppService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class EptGroupResource extends Resource
{
    protected static ?string $model = EptGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'EPT';
    protected static ?string $navigationLabel = 'Grup EPT';
    protected static ?string $modelLabel = 'Grup EPT';
    protected static ?string $pluralModelLabel = 'Grup EPT';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Grup')
                    ->required()
                    ->placeholder('Contoh: Grup 001 Pasca'),
                Forms\Components\DateTimePicker::make('jadwal')
                    ->label('Jadwal Tes')
                    ->native(false)
                    ->seconds(false)
                    ->nullable(),
                Forms\Components\TextInput::make('lokasi')
                    ->label('Lokasi')
                    ->default('Ruang Stanford')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Grup')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jadwal')
                    ->label('Jadwal Tes')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('Belum ditetapkan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('peserta_count')
                    ->label('Jumlah Peserta')
                    ->alignCenter()
                    ->getStateUsing(fn (EptGroup $record) => 
                        $record->allRegistrations()->count()
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('has_jadwal')
                    ->label('Status Jadwal')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah ada jadwal')
                    ->falseLabel('Belum ada jadwal')
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('jadwal'),
                        false: fn ($q) => $q->whereNull('jadwal'),
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    // ACTION: TETAPKAN JADWAL
                    Tables\Actions\Action::make('tetapkan_jadwal')
                        ->label('Tetapkan Jadwal')
                        ->icon('heroicon-o-calendar')
                        ->color('success')
                        ->visible(fn (EptGroup $record) => !$record->jadwal)
                        ->form([
                            Forms\Components\DateTimePicker::make('jadwal')
                                ->label('Waktu Tes')
                                ->required()
                                ->native(false)
                                ->seconds(false)
                                ->default(now()->setTime(8, 30))
                                ->minDate(now()->startOfDay()),
                        ])
                        ->action(function (EptGroup $record, array $data) {
                            $record->update(['jadwal' => $data['jadwal']]);
                            Notification::make()
                                ->success()
                                ->title('Jadwal berhasil ditetapkan')
                                ->body('Pendaftar sekarang bisa melihat jadwal di dashboard.')
                                ->send();
                        }),

                    // ACTION: KIRIM NOTIF WA
                    Tables\Actions\Action::make('kirim_notif_wa')
                        ->label('Kirim Notif WA')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('info')
                        ->visible(fn (EptGroup $record) => $record->jadwal !== null)
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Notifikasi WhatsApp')
                        ->modalDescription(fn (EptGroup $record) => 
                            'Kirim notifikasi jadwal ke semua peserta grup "' . $record->name . '" (' . $record->allRegistrations()->count() . ' peserta)?'
                        )
                        ->action(function (EptGroup $record) {
                            $registrations = $record->allRegistrations()->with('user')->get();
                            $sent = 0;
                            $failed = 0;

                            foreach ($registrations as $reg) {
                                $user = $reg->user;
                                if (!$user->whatsapp || !$user->whatsapp_verified_at) {
                                    $failed++;
                                    continue;
                                }

                                try {
                                    // Determine which tes number
                                    $tesNum = match(true) {
                                        $reg->grup_1_id === $record->id => 1,
                                        $reg->grup_2_id === $record->id => 2,
                                        $reg->grup_3_id === $record->id => 3,
                                        default => null,
                                    };
                                    
                                    $jadwal = $record->jadwal->translatedFormat('l, d F Y H:i');
                                    $dashboardUrl = route('dashboard.ept-registration.index');

                                    $message = "*Jadwal Tes EPT Ditetapkan*\n\n";
                                    $message .= "Yth. *{$user->name}*,\n\n";
                                    $message .= "Jadwal *Tes ke-{$tesNum}* EPT Anda telah ditetapkan:\n\n";
                                    $message .= "*Grup:* {$record->name}\n";
                                    $message .= "*Waktu:* {$jadwal} WIB\n";
                                    $message .= "*Lokasi:* {$record->lokasi}\n\n";
                                    $message .= "Silakan download Kartu Peserta melalui:\n{$dashboardUrl}\n\n";
                                    $message .= "_Wajib membawa kartu peserta dan KTP/Kartu Mahasiswa._";

                                    app(WhatsAppService::class)->sendMessage($user->whatsapp, $message);
                                    $sent++;
                                } catch (\Exception $e) {
                                    $failed++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title('Notifikasi terkirim')
                                ->body("Berhasil: {$sent}, Gagal: {$failed}")
                                ->send();
                        }),

                    Tables\Actions\Action::make('export_bukti_pembayaran')
                        ->label('Export Bukti')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('warning')
                        ->visible(fn () => auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi']))
                        ->action(function (EptGroup $record) {
                            return redirect()->to(route('admin.ept-group-export-bukti.preview', [
                                'group' => $record->id,
                            ]));
                        }),
                    
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Peserta'),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-cog-6-tooth'),
            ])
            ->bulkActions([
                // BULK: KIRIM NOTIF WA KE BANYAK GRUP
                Tables\Actions\BulkAction::make('bulk_kirim_notif')
                    ->label('Kirim Notif WA')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $totalSent = 0;
                        $totalFailed = 0;

                        foreach ($records as $record) {
                            if (!$record->jadwal) continue;

                            $registrations = $record->allRegistrations()->with('user')->get();

                            foreach ($registrations as $reg) {
                                $user = $reg->user;
                                if (!$user->whatsapp || !$user->whatsapp_verified_at) {
                                    $totalFailed++;
                                    continue;
                                }

                                try {
                                    // Determine which tes number
                                    $tesNum = match(true) {
                                        $reg->grup_1_id === $record->id => 1,
                                        $reg->grup_2_id === $record->id => 2,
                                        $reg->grup_3_id === $record->id => 3,
                                        default => null,
                                    };
                                    
                                    $jadwal = $record->jadwal->translatedFormat('l, d F Y H:i');
                                    $dashboardUrl = route('dashboard.ept-registration.index');

                                    $message = "*Jadwal Tes EPT Ditetapkan*\n\n";
                                    $message .= "Yth. *{$user->name}*,\n\n";
                                    $message .= "Jadwal *Tes ke-{$tesNum}* EPT Anda telah ditetapkan:\n\n";
                                    $message .= "*Grup:* {$record->name}\n";
                                    $message .= "*Waktu:* {$jadwal} WIB\n";
                                    $message .= "*Lokasi:* {$record->lokasi}\n\n";
                                    $message .= "Silakan download Kartu Peserta melalui:\n{$dashboardUrl}\n\n";
                                    $message .= "_Wajib membawa kartu peserta dan KTP/Kartu Mahasiswa._";

                                    app(WhatsAppService::class)->sendMessage($user->whatsapp, $message);
                                    $totalSent++;
                                } catch (\Exception $e) {
                                    $totalFailed++;
                                }
                            }
                        }

                        Notification::make()
                            ->success()
                            ->title('Notifikasi terkirim')
                            ->body("Berhasil: {$totalSent}, Gagal: {$totalFailed}")
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEptGroups::route('/'),
            'create' => Pages\CreateEptGroup::route('/create'),
            'view' => Pages\ViewEptGroup::route('/{record}'),
            'edit' => Pages\EditEptGroup::route('/{record}/edit'),
        ];
    }
}
