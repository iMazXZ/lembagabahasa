<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataNilaiTesResource\Pages;
use App\Models\DataNilaiTes;
use App\Models\PendaftaranGrupTes;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use App\Notifications\NilaiEptNotification;
use Filament\Notifications\Notification;
use App\Notifications\SertifikatSiapDiambilNotification;

class DataNilaiTesResource extends Resource
{
    protected static ?string $model = DataNilaiTes::class;

    protected static ?string $navigationIcon = 'heroicon-s-clipboard-document-check';
    
    protected static ?string $navigationGroup = 'Manajemen EPT';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('pendaftaran_grup_tes_id')
                ->label('Nama Peserta')
                ->relationship('pendaftaranGrupTes', 'id', function ($query) {
                    $query->with(['pendaftaranEpt.users', 'masterGrupTes', 'dataNilaiTes']);
                })
                ->getOptionLabelFromRecordUsing(fn ($record) =>
                    $record->pendaftaranEpt->users->name . ' - ' .
                    $record->pendaftaranEpt->users->srn . ' - ' .
                    'Grup ' . $record->masterGrupTes->group_number .
                    ($record->dataNilaiTes ? ' (Score: ' . $record->dataNilaiTes->total_score . ')' : '')
                )
                ->required(),

            Forms\Components\TextInput::make('listening_comprehension')
                ->label('Listening Comprehension')
                ->numeric()
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (callable $get, callable $set) {
                    self::updateScoreAndRank($get, $set);
                }),

            Forms\Components\TextInput::make('structure_written_expr')
                ->label('Structure and Written Expression')
                ->numeric()
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (callable $get, callable $set) {
                    self::updateScoreAndRank($get, $set);
                }),

            Forms\Components\TextInput::make('reading_comprehension')
                ->label('Reading Comprehension')
                ->numeric()
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (callable $get, callable $set) {
                    self::updateScoreAndRank($get, $set);
                }),

            Forms\Components\TextInput::make('total_score')
                ->label('Total Score')
                ->helperText('Akan dihitung otomatis')
                ->numeric()
                ->disabled()
                ->dehydrated()
                ->afterStateHydrated(function ($component, $state) {
                    $component->state($state);
                })
                ->default(0)
                ->helperText('Akan dihitung otomatis'),

            Forms\Components\TextInput::make('rank')
                ->label('Rank')
                ->disabled()
                ->dehydrated()
                ->afterStateHydrated(function ($component, $state) {
                    $component->state($state);
                })
                ->helperText('Akan dihitung otomatis'),

            Forms\Components\Hidden::make('selesai_pada')
                ->default(now())
                ->dehydrated(),
        ]);
    }

    private static function updateScoreAndRank(callable $get, callable $set): void
    {
        $listening = (int) ($get('listening_comprehension') ?? 0);
        $structure = (int) ($get('structure_written_expr') ?? 0);
        $reading = (int) ($get('reading_comprehension') ?? 0);
        
        $totalScore = $listening + $structure + $reading;
        $set('total_score', $totalScore);
                
        $rank = $totalScore >= 400 ? 'Pass' : 'Fail';
        $set('rank', $rank);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('pendaftaranGrupTes.pendaftaranEpt.users.name')
                ->label('Nama Peserta')
                ->searchable(),
            Tables\Columns\TextColumn::make('pendaftaranGrupTes.pendaftaranEpt.users.srn')
                ->label('SRN')
                ->searchable(),
            Tables\Columns\TextColumn::make('pendaftaranGrupTes.masterGrupTes.group_number')
                ->label('Grup')
                ->sortable(),
            Tables\Columns\BadgeColumn::make('listening_comprehension')->label('Listening'),
            Tables\Columns\BadgeColumn::make('structure_written_expr')->label('Structure'),
            Tables\Columns\BadgeColumn::make('reading_comprehension')->label('Reading'),
            Tables\Columns\BadgeColumn::make('total_score')->label('Total'),
            Tables\Columns\BadgeColumn::make('rank')
                ->label('Status')
                ->color(fn ($state) => match ($state) {
                    'Fail' => 'danger',
                    'Pass' => 'success',
                    default => null,
                }),
            Tables\Columns\TextColumn::make('selesai_pada')
                ->label('Input Pada')
                ->dateTime('d M Y H:i'),
        ])
        ->defaultSort('updated_at', 'desc')
        ->actions([
            Tables\Actions\ActionGroup::make([
            Action::make('kirim_nilai_email')
                ->label('Kirim Email Nilai')                
                ->icon('heroicon-s-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => !$record->email_nilai_terkirim)
                ->action(function ($record) {
                    $user = $record->pendaftaranGrupTes->pendaftaranEpt->users;
                    $tanggal = $record->pendaftaranGrupTes->masterGrupTes->tanggal ?? now();

                    if ($user) {
                        $user->notify(new NilaiEptNotification($record, $tanggal));
                        $record->update(['email_nilai_terkirim' => true]);
                        Notification::make()->title('Notifikasi Nilai Berhasil dikirim ke ' . $user->name)->success()->send();
                    }
                }),
            Action::make('kirim_notif_sertifikat')
                ->label('Kirim Notif Sertifikat')                
                ->icon('heroicon-s-paper-airplane')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn ($record) => 
                    $record->rank === 'Pass' && !$record->sertifikat_notif_terkirim
                )
                ->action(function ($record) {
                    $user = $record->pendaftaranGrupTes->pendaftaranEpt->users;

                    if ($user) {
                        $user->notify(new SertifikatSiapDiambilNotification($user));
                        $record->update(['sertifikat_notif_terkirim' => true]);
                        Notification::make()->title('Notifikasi Sertifikat Berhasil dikirim ke ' . $user->name)->success()->send();
                    }
                }),
            ])->label('Kirim Email')
                    ->icon('heroicon-s-paper-airplane')
                    ->color('danger')
                    ->button(),

            Tables\Actions\EditAction::make()
                ->label(' '),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),

                Tables\Actions\BulkAction::make('kirim_nilai_email')
                    ->label('Kirim Email Nilai')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $user = $record->pendaftaranGrupTes->pendaftaranEpt->users;
                            $tanggal = $record->pendaftaranGrupTes->masterGrupTes->tanggal ?? now();

                            if ($user) {
                                $user->notify(new NilaiEptNotification($record, $tanggal));
                                $record->update(['email_nilai_terkirim' => true]);
                            }
                        }
                    }),
                    
                Tables\Actions\BulkAction::make('kirimNotifSertifikat')
                    ->label('Kirim Notif Sertifikat Siap Diambil')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $berhasilKirim = 0;                        
                        $records->where('sertifikat_notif_terkirim', false)->each(function ($record) use (&$berhasilKirim) {
                            $user = $record->pendaftaranGrupTes->pendaftaranEpt->user;

                            if ($user) {
                                $user->notify(new SertifikatSiapDiambilNotification($user));
                                $record->update(['sertifikat_notif_terkirim' => true]);
                                $berhasilKirim++;
                            }
                        });

                        Notification::make()
                            ->title('Notifikasi berhasil dikirim ke ' . $berhasilKirim . ' peserta.')
                            ->success()
                            ->send();
                    }),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataNilaiTes::route('/'),
            'create' => Pages\CreateDataNilaiTes::route('/create'),
            'edit' => Pages\EditDataNilaiTes::route('/{record}/edit'),
        ];
    }
}