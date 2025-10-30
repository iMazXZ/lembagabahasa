<?php

namespace App\Filament\Pages;

use App\Models\BasicListeningSurveyResponse;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SurveyResponses extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Survey Responses';
    protected static ?string $title = 'Survey Responses';
    protected static ?string $navigationGroup = 'Basic Listening';
    protected static ?string $navigationParentItem = 'Kuesioner';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.survey-responses';

    public function table(Table $table): Table
    {
        return $table
            ->query(BasicListeningSurveyResponse::query()
                ->with(['survey', 'user', 'tutor', 'supervisor'])
                ->withCount('answers')
                ->withAvg('answers', 'likert_value')
            )
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                
                TextColumn::make('survey.title')->label('Survey')->sortable()->searchable(),
                
                TextColumn::make('survey.category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'tutor' => 'Tutor', 'supervisor' => 'Supervisor', 'institute' => 'Lembaga',
                        default => ucfirst($state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'tutor' => 'primary', 'supervisor' => 'success', 'institute' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                    
                TextColumn::make('user.name')->label('Mahasiswa')->sortable()->searchable(),
                
                TextColumn::make('tutor.name')->label('Tutor')->placeholder('—'),
                
                TextColumn::make('supervisor.name')->label('Supervisor')->placeholder('—'),
                
                TextColumn::make('answers_count')
                    ->label('Jumlah Jawaban')
                    ->sortable()
                    ->alignCenter(),
                    
                TextColumn::make('answers_avg_likert_value')
                    ->label('Rata-rata')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) : '—')
                    ->color(fn ($state) => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 3.5 => 'warning', 
                        $state >= 1 => 'danger',
                        default => 'gray',
                    })
                    ->alignCenter()
                    ->sortable(),
                    
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('survey')
                    ->relationship('survey', 'title')
                    ->searchable()
                    ->preload(),
                    
                \Filament\Tables\Filters\SelectFilter::make('category')
                    ->form(fn() => [
                        \Filament\Forms\Components\Select::make('category')
                            ->options([
                                'tutor' => 'Tutor',
                                'supervisor' => 'Supervisor', 
                                'institute' => 'Lembaga',
                            ])
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['category'])) {
                            $query->whereHas('survey', fn($q) => $q->where('category', $data['category']));
                        }
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (BasicListeningSurveyResponse $record) =>
                        \App\Filament\Pages\SurveyResponsesDetail::getUrl(['record' => $record->id])
                    ),
                    
                \Filament\Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Response?')
                    ->modalDescription('Jawaban (answers) yang terkait juga akan dihapus.'),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\DeleteBulkAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Response Terpilih?')
                    ->modalDescription('Semua jawaban (answers) yang terkait juga akan dihapus.'),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->emptyStateHeading('Belum ada data response')
            ->emptyStateDescription('Data akan muncul setelah mahasiswa mengisi kuesioner.');
    }

    public static function canAccess(): bool
    {
        return true;
    }
}