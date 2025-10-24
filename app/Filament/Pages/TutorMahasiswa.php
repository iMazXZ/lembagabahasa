<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Prody;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Pages\Actions\Action;

/**
 * Page ini menampilkan daftar mahasiswa yang:
 * - prodi_id termasuk prodi yang diampu tutor (auth user),
 * - SRN/NPM diawali prefix angkatan (default "25"),
 * - Admin bisa melihat semua (tanpa batasan).
 *
 * Catatan: Kolom SRN = 'srn', Prodi = relasi 'prody'.
 */
class TutorMahasiswa extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Mahasiswa Binaan';
    protected static ?string $title = 'Mahasiswa Binaan (Tutor)';
    protected static ?string $navigationGroup = 'Basic Listening';
    protected static ?int $navigationSort = 15;

    protected static string $view = 'filament.pages.tutor-mahasiswa';

    /** Batasi akses: hanya admin atau tutor. */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->hasRole('admin') || $user?->hasRole('tutor');
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query($this->baseQuery($user))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('srn')
                    ->label('NPM')
                    ->searchable(),

                Tables\Columns\TextColumn::make('prody.name')
                    ->label('Prodi')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('attempts_count')
                    ->label('Jumlah Attempt')
                    ->counts('basicListeningAttempts')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('last_attempt_at')
                    ->label('Attempt Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->state(function (User $record) {
                        $last = $record->basicListeningAttempts()
                            ->latest('updated_at')
                            ->select(['id', 'submitted_at', 'updated_at'])
                            ->first();

                        return $last?->submitted_at ?? $last?->updated_at;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // ✅ Filter Angkatan pakai Filter + TextInput
                Filter::make('angkatan')
                    ->label('Prefix Angkatan (SRN)')
                    ->form([
                        Forms\Components\TextInput::make('prefix')
                            ->placeholder('mis. 25')
                            ->default('25')
                            ->maxLength(2)
                            ->datalist(['25', '24', '23']),
                    ])
                    ->indicateUsing(fn(array $data): ?string =>
                        filled($data['prefix'] ?? null)
                            ? 'Angkatan: ' . $data['prefix']
                            : null
                    )
                    ->query(function (Builder $query, array $data) {
                        $prefix = trim((string)($data['prefix'] ?? ''));
                        if ($prefix !== '') {
                            $query->where('srn', 'like', $prefix . '%');
                        }
                    }),

                // Filter Prodi
                Tables\Filters\SelectFilter::make('prody_id')
                    ->label('Prodi')
                    ->options(function () use ($user) {
                        if ($user?->hasRole('tutor')) {
                            return Prody::query()
                                ->whereIn('id', $user->assignedProdyIds())
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        }

                        return Prody::query()
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('prody_id', $data['value']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('Lihat Attempts')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->url(fn(User $record) =>
                        route('filament.admin.resources.basic-listening-attempts.index', [
                            // opsional: tambahkan filter ke resource jika tersedia
                            // 'table[filters][user_id][value]' => $record->id,
                        ])
                    )
                    ->openUrlInNewTab()
                    ->visible(fn() => $user?->can('view_any_basic::listening::attempt')),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada data')
            ->emptyStateDescription('Ubah filter angkatan atau pastikan prodi yang Anda ampu sudah diatur.');
    }

    /** Query dasar: scope ke prodi tutor + prefix SRN default "25". */
    protected function baseQuery(?User $user): Builder
    {
        $query = User::query()
            ->with(['prody'])
            ->whereNotNull('srn');

        if ($user?->hasRole('admin')) {
            return $query;
        }

        if ($user?->hasRole('tutor')) {
            $ids = $user->assignedProdyIds();
            if (empty($ids)) {
                return $query->whereRaw('1=0');
            }

            return $query
                ->whereIn('prody_id', $ids)
                ->where('srn', 'like', '25%');
        }

        return $query->whereRaw('1=0');
    }

    protected function getActions(): array
    {
        return [
            Action::make('dashboard')
                ->label('Kembali ke Dasbor')
                ->url(route('filament.admin.pages.2'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
