<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EptQuizResource\Pages;
use App\Models\EptQuiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EptQuizResource extends Resource
{
    protected static ?string $model = EptQuiz::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'EPT';
    protected static ?string $pluralLabel = 'Paket Soal EPT';
    protected static ?string $modelLabel = 'Paket Soal';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Paket')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Paket Soal')
                        ->placeholder('EPT Januari 2026')
                        ->required()
                        ->maxLength(255),
                    
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi')
                        ->placeholder('Deskripsi paket soal...')
                        ->rows(3),
                    
                    Forms\Components\FileUpload::make('listening_audio_url')
                        ->label('Audio Listening (MP3)')
                        ->disk('public')
                        ->directory('ept/audio')
                        ->acceptedFileTypes(['audio/mpeg', 'audio/mp3'])
                        ->maxSize(102400) // 100MB
                        ->helperText('Upload file audio listening (1 file untuk seluruh section)'),
                    
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])
                ->columns(1),
            
            Forms\Components\Section::make('Durasi per Section (menit)')
                ->schema([
                    Forms\Components\TextInput::make('listening_duration')
                        ->label('Listening')
                        ->numeric()
                        ->default(35)
                        ->suffix('menit')
                        ->required(),
                    
                    Forms\Components\TextInput::make('structure_duration')
                        ->label('Structure')
                        ->numeric()
                        ->default(25)
                        ->suffix('menit')
                        ->required(),
                    
                    Forms\Components\TextInput::make('reading_duration')
                        ->label('Reading')
                        ->numeric()
                        ->default(55)
                        ->suffix('menit')
                        ->required(),
                ])
                ->columns(3),
            
            Forms\Components\Section::make('Jumlah Soal per Section')
                ->schema([
                    Forms\Components\TextInput::make('listening_count')
                        ->label('Listening')
                        ->numeric()
                        ->default(50)
                        ->suffix('soal')
                        ->required(),
                    
                    Forms\Components\TextInput::make('structure_count')
                        ->label('Structure')
                        ->numeric()
                        ->default(40)
                        ->suffix('soal')
                        ->required(),
                    
                    Forms\Components\TextInput::make('reading_count')
                        ->label('Reading')
                        ->numeric()
                        ->default(50)
                        ->suffix('soal')
                        ->required(),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Paket')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_questions')
                    ->label('Total Soal')
                    ->getStateUsing(fn ($record) => 
                        $record->listening_count + $record->structure_count + $record->reading_count
                    )
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('total_duration')
                    ->label('Total Durasi')
                    ->getStateUsing(fn ($record) => 
                        ($record->listening_duration + $record->structure_duration + $record->reading_duration) . ' menit'
                    ),
                
                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Soal Dibuat')
                    ->counts('questions')
                    ->badge()
                    ->color(fn ($state, $record) => 
                        $state >= ($record->listening_count + $record->structure_count + $record->reading_count) 
                            ? 'success' 
                            : 'warning'
                    ),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\Action::make('import_soal')
                    ->label('Import Soal')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('file')
                            ->label('File Excel')
                            ->disk('local')
                            ->directory('temp')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->required()
                            ->helperText('Format: section, order, question, option_a, option_b, option_c, option_d, correct_answer, passage, passage_group'),
                    ])
                    ->action(function (array $data, $record) {
                        $path = storage_path('app/' . $data['file']);
                        
                        try {
                            \Maatwebsite\Excel\Facades\Excel::import(
                                new \App\Imports\EptQuestionsImport($record->id),
                                $path
                            );
                            
                            // Cleanup
                            if (file_exists($path)) unlink($path);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Import berhasil!')
                                ->body('Soal berhasil diimport.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Import gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Tables\Actions\Action::make('download_template')
                    ->label('Template Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\EptQuestionsTemplateExport(),
                            'ept_questions_template.xlsx'
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Will add QuestionsRelationManager later
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEptQuizzes::route('/'),
            'create' => Pages\CreateEptQuiz::route('/create'),
            'edit' => Pages\EditEptQuiz::route('/{record}/edit'),
            'view' => Pages\ViewEptQuiz::route('/{record}'),
        ];
    }
}
