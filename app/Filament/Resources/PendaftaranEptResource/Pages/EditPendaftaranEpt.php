<?php

namespace App\Filament\Resources\PendaftaranEptResource\Pages;

use App\Filament\Resources\PendaftaranEptResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPendaftaranEpt extends EditRecord
{
    protected static string $resource = PendaftaranEptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    public function getSubheading(): ?string
    {
        if ($this->record && $this->record->status_pembayaran === 'rejected') {
            return 'Silakan upload ulang bukti pembayaran Anda, kemudian klik tombol Perbarui Permohonan.';
        }
        return null;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Permohonan diperbarui')
            ->body('Data pendaftaran ept berhasil diperbarui.');
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()->hasRole(['Admin', 'Staf Administrasi'])) {
            return [
                Actions\DeleteAction::make(),
            ];
        }

        return [];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Perbarui Permohonan')
                ->submit('save')
                ->icon('heroicon-o-check-circle'),
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-x-circle'),
        ];
    }
}
