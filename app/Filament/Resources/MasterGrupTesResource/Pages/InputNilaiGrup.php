<?php

namespace App\Filament\Resources\MasterGrupTesResource\Pages;

use App\Filament\Resources\MasterGrupTesResource;
use App\Models\DataNilaiTes;
use App\Models\PendaftaranGrupTes;
use Filament\Forms;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class InputNilaiGrup extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = MasterGrupTesResource::class;

    protected static string $view = 'filament.resources.master-grup-tes-resource.pages.input-nilai-grup';

    public $record;

    public array $nilai = [];

    public function mount($record): void
    {
        $this->record = MasterGrupTesResource::resolveRecordRouteBinding($record);

        $this->nilai = $this->record->pendaftaranGrupTes->map(function ($item) {
            $user = $item->pendaftaranEpt->users;

            $existing = DataNilaiTes::where('pendaftaran_grup_tes_id', $item->id)->first();

            return [
                'id' => $item->id,
                'nama' => $user->name,
                'srn' => $user->srn,
                'listening' => $existing->listening_comprehension ?? null,
                'structure' => $existing->structure_written_expr ?? null,
                'reading' => $existing->reading_comprehension ?? null,
                'total' => $existing->total_score ?? null,
                'rank' => $existing->rank ?? null,
            ];
        })->toArray();
    }

    public function save(): void
    {
        foreach ($this->nilai as $data) {
            $total = (int) $data['listening'] + (int) $data['structure'] + (int) $data['reading'];
            $rank = $total >= 400 ? 'Pass' : 'Fail';

            DataNilaiTes::updateOrCreate(
                ['pendaftaran_grup_tes_id' => $data['id']],
                [
                    'listening_comprehension' => $data['listening'],
                    'structure_written_expr' => $data['structure'],
                    'reading_comprehension' => $data['reading'],
                    'total_score' => $total,
                    'rank' => $rank,
                    'selesai_pada' => now(),
                ]
            );
        }

        Notification::make()
            ->title('Nilai berhasil disimpan')
            ->success()
            ->send();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Repeater::make('nilai')
                ->label('Nilai Peserta')
                ->schema([
                    Forms\Components\TextInput::make('nama')->disabled(),
                    Forms\Components\TextInput::make('srn')->disabled(),
                    Forms\Components\TextInput::make('listening')
                        ->numeric()->minValue(0)->maxValue(200)->required(),
                    Forms\Components\TextInput::make('structure')
                        ->numeric()->minValue(0)->maxValue(200)->required(),
                    Forms\Components\TextInput::make('reading')
                        ->numeric()->minValue(0)->maxValue(200)->required(),
                ])
                ->columns(6)
                ->default($this->nilai)
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('Simpan')
                ->label('Simpan Nilai')
                ->action('save')
                ->color('success'),
        ];
    }
}

