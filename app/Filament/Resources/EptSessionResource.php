<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EptSessionResource\Pages;
use App\Models\EptSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EptSessionResource extends Resource
{
    protected static ?string $model = EptSession::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'EPT';
    protected static ?string $pluralLabel = 'Sesi Ujian';
    protected static ?string $modelLabel = 'Sesi';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Sesi')
                ->schema([
                    Forms\Components\Select::make('quiz_id')
                        ->label('Paket Soal')
                        ->relationship('quiz', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Sesi')
                        ->placeholder('Sesi 1 - Pagi')
                        ->required(),
                    
                    Forms\Components\Select::make('mode')
                        ->label('Mode')
                        ->options([
                            'online' => 'Online',
                            'offline' => 'Offline',
                        ])
                        ->default('offline')
                        ->required()
                        ->reactive(),
                    
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])
                ->columns(2),
            
            Forms\Components\Section::make('Jadwal')
                ->schema([
                    Forms\Components\DatePicker::make('date')
                        ->label('Tanggal')
                        ->required()
                        ->native(false),
                    
                    Forms\Components\TimePicker::make('start_time')
                        ->label('Jam Mulai')
                        ->required()
                        ->seconds(false),
                    
                    Forms\Components\TimePicker::make('end_time')
                        ->label('Jam Selesai')
                        ->required()
                        ->seconds(false),
                    
                    Forms\Components\TextInput::make('max_participants')
                        ->label('Maks Peserta')
                        ->numeric()
                        ->default(20)
                        ->required(),
                ])
                ->columns(4),
            
            Forms\Components\Section::make('Keamanan')
                ->schema([
                    Forms\Components\TextInput::make('passcode')
                        ->label('Passcode Pengawas')
                        ->placeholder('Akan diberikan pengawas saat ujian')
                        ->helperText('Kode ini diberikan pengawas saat ujian dimulai'),
                ]),
            
            Forms\Components\Section::make('Zoom (Online)')
                ->schema([
                    Forms\Components\TextInput::make('zoom_meeting_id')
                        ->label('Meeting ID'),
                    
                    Forms\Components\TextInput::make('zoom_passcode')
                        ->label('Zoom Passcode'),
                    
                    Forms\Components\TextInput::make('zoom_link')
                        ->label('Link Zoom')
                        ->url(),
                ])
                ->columns(3)
                ->visible(fn ($get) => $get('mode') === 'online')
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Sesi')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('quiz.name')
                    ->label('Paket Soal')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Waktu')
                    ->formatStateUsing(fn ($record) => $record->start_time . ' - ' . $record->end_time),
                
                Tables\Columns\TextColumn::make('mode')
                    ->label('Mode')
                    ->badge()
                    ->color(fn ($state) => $state === 'online' ? 'info' : 'gray')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
                Tables\Columns\TextColumn::make('registrations_count')
                    ->label('Peserta')
                    ->counts('registrations')
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('mode')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\Action::make('release_tokens')
                    ->label('Release Token')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Release Token CBT')
                    ->modalDescription('Semua peserta yang terdaftar di sesi ini akan menerima token CBT mereka.')
                    ->action(function (EptSession $record) {
                        $count = 0;
                        foreach ($record->registrations()->whereNull('cbt_token')->get() as $reg) {
                            $reg->generateToken();
                            $count++;
                        }
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title("$count token berhasil dirilis!")
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEptSessions::route('/'),
            'create' => Pages\CreateEptSession::route('/create'),
            'edit' => Pages\EditEptSession::route('/{record}/edit'),
        ];
    }
}
