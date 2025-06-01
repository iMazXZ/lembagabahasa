<?php

namespace App\Filament\Resources\PendaftaranEptResource\Pages;

use App\Filament\Resources\PendaftaranEptResource;
use Filament\Resources\Pages\Page;
use App\Models\PendaftaranEpt;

class ViewPendaftaranPage extends Page
{
    protected static string $resource = PendaftaranEptResource::class;

    protected static string $view = 'filament.resources.pendaftaran-ept-resource.pages.view-pendaftaran-page';

    public $record;

    public function mount($record): void
    {
        $this->record = PendaftaranEpt::with([
            'pendaftaranGrupTes.masterGrupTes',
            'pendaftaranGrupTes.dataNilaiTes'
        ])->findOrFail($record);
    }

    public function getTitle(): \Illuminate\Contracts\Support\Htmlable|string
    {
        return ''; // judul kosong
    }

    public function getBreadcrumbs(): array
    {
        return [
            url(route('filament.resources.pendaftaran-epts.index')) => 'Pendaftaran EPT',
            '#' => 'Detail',
        ];
    }
}
