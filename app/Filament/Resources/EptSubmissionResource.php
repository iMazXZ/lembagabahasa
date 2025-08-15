<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EptSubmissionResource\Pages;
use App\Models\EptSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class EptSubmissionResource extends Resource
{
    protected static ?string $model = EptSubmission::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Pengajuan Surat Rekomendasi';
    protected static ?string $pluralModelLabel = 'Pengajuan Surat Rekomendasi';

    // Halaman ini HANYA muncul untuk role selain 'Pendaftar'
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi', 'Kepala Lembaga']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]); // Form tidak kita gunakan karena verifikasi via tabel
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Nama Pendaftar')->searchable(),
                Tables\Columns\TextColumn::make('user.srn')->label('NPM')->searchable(),
                Tables\Columns\TextColumn::make('user.prody.name')->label('Prodi')->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Pada')
                    ->dateTime(fn () => request()->header('User-Agent') && preg_match('/Mobile|Android|iPhone|iPad|iPod/i', request()->header('User-Agent')) ? 'd/m' : 'd/m/Y H:i')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('tanggal_tes_1')->label('Tgl Tes Utama')->date()->sortable(),
                // Tables\Columns\TextColumn::make('nilai_tes_1')->label('Nilai Utama')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger',
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tombol Approve: Hanya untuk Staf
                Action::make('approve')
                    ->label('Approve')->icon('heroicon-o-check-circle')->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('catatan_admin')
                            ->label('Catatan (Opsional)')
                    ])
                    ->action(function (EptSubmission $record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'catatan_admin' => $data['catatan_admin']
                        ]);
                    })
                    ->visible(fn (EptSubmission $record): bool => $record->status === 'pending' && auth()->user()->hasRole('Admin')),

                // Tombol Reject: Hanya untuk Staf
                Action::make('reject')
                    ->label('Reject')->icon('heroicon-o-x-circle')->color('danger')
                    ->requiresConfirmation()
                    ->form([Forms\Components\Textarea::make('catatan_admin')->label('Alasan Penolakan')->required()])
                    ->action(function (EptSubmission $record, array $data) {
                        $record->update(['status' => 'rejected', 'catatan_admin' => $data['catatan_admin']]);
                    })
                    ->visible(fn (EptSubmission $record): bool => $record->status === 'pending' && auth()->user()->hasRole('Admin')),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Informasi Pendaftar')
                    ->schema([
                        Components\TextEntry::make('user.name')->label('Nama Pendaftar'),
                        Components\TextEntry::make('user.srn')->label('NPM'),
                        Components\TextEntry::make('user.prody.name')->label('Prodi'),
                        Components\TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => $state,
                            }),
                        Components\TextEntry::make('catatan_admin')->label('Catatan dari Staf'),
                    ])->columns(3),

                Components\Section::make('Data Tes 1')
                    ->schema([
                        Components\TextEntry::make('nilai_tes_1')->label('Nilai'),
                        Components\TextEntry::make('tanggal_tes_1')->label('Tanggal')->date(),
                        Components\TextEntry::make('foto_path_1')
                            ->label('Bukti Foto')
                            ->formatStateUsing(function ($state) {
                                if (!$state) return '-';
                                $url = \Storage::disk('public')->url($state);
                                return "<a href=\"{$url}\" target=\"_blank\"><img src=\"{$url}\" alt=\"Bukti Foto\" style=\"max-width:80px;max-height:80px;border-radius:6px;box-shadow:0 1px 4px #0002\"></a>";
                            })
                            ->html(),
                    ])->columns(3),

                Components\Section::make('Data Tes 2')
                    ->schema([
                        Components\TextEntry::make('nilai_tes_2')->label('Nilai'),
                        Components\TextEntry::make('tanggal_tes_2')->label('Tanggal')->date(),
                        Components\TextEntry::make('foto_path_2')
                            ->label('Bukti Foto')
                            ->formatStateUsing(function ($state) {
                                if (!$state) return '-';
                                $url = \Storage::disk('public')->url($state);
                                return "<a href=\"{$url}\" target=\"_blank\"><img src=\"{$url}\" alt=\"Bukti Foto\" style=\"max-width:80px;max-height:80px;border-radius:6px;box-shadow:0 1px 4px #0002\"></a>";
                            })
                            ->html(),
                    ])->columns(3)->visible(fn ($record) => !empty($record->nilai_tes_2)),

                Components\Section::make('Data Tes 3')
                    ->schema([
                        Components\TextEntry::make('nilai_tes_3')->label('Nilai'),
                        Components\TextEntry::make('tanggal_tes_3')->label('Tanggal')->date(),
                        Components\TextEntry::make('foto_path_3')
                            ->label('Bukti Foto')
                            ->formatStateUsing(function ($state) {
                                if (!$state) return '-';
                                $url = \Storage::disk('public')->url($state);
                                return "<a href=\"{$url}\" target=\"_blank\"><img src=\"{$url}\" alt=\"Bukti Foto\" style=\"max-width:80px;max-height:80px;border-radius:6px;box-shadow:0 1px 4px #0002\"></a>";
                            })
                            ->html(),
                    ])->columns(3)->visible(fn ($record) => !empty($record->nilai_tes_3)),
            ]);
    }

    // Atur halaman yang tersedia untuk resource ini
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEptSubmissions::route('/'),
            // Halaman create & edit tidak diperlukan oleh admin
        ];
    }

    // Admin tidak bisa membuat data dari sini
    public static function canCreate(): bool
    {
        return false;
    }
}