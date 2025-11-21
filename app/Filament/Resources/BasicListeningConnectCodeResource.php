<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BasicListeningConnectCodeResource\Pages;
use App\Models\BasicListeningConnectCode;
use App\Models\BasicListeningQuiz;
use App\Models\Prody;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BasicListeningConnectCodeResource extends Resource
{
    protected static ?string $model = BasicListeningConnectCode::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Basic Listening';
    protected static ?string $pluralLabel = 'Connect Codes';
    protected static ?string $modelLabel  = 'Connect Code';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Sesi')
                    ->schema([
                        Forms\Components\Select::make('session_id')
                            ->relationship('session', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live() // Gunakan live agar real-time
                            ->afterStateUpdated(fn (Set $set) => $set('quiz_id', null)) // Reset quiz jika sesi ganti
                            ->label('Pilih Meeting'),

                        Forms\Components\Select::make('quiz_id')
                            ->label('Untuk Paket Soal Apa (Opsional)')
                            ->options(function (Get $get) { // Gunakan Get $get
                                $sessionId = $get('session_id');
                                if (!$sessionId) return [];
                                
                                return BasicListeningQuiz::where('session_id', $sessionId)
                                    ->where('is_active', true) // Filter hanya quiz aktif (opsional)
                                    ->orderBy('title')
                                    ->pluck('title', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('Kosongkan jika ingin berlaku untuk semua quiz di sesi ini.'),
                    ])->columns(2),

                Forms\Components\Section::make('Konfigurasi Kode')
                    ->schema([
                        Forms\Components\TextInput::make('plain_code')
                            ->label('Kode Akses')
                            ->helperText(fn (string $operation) => $operation === 'create' 
                                ? 'Kode yang akan dibagikan ke mahasiswa.' 
                                : 'Biarkan kosong jika tidak ingin mengubah kode.')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            // Wajib saat create, opsional saat edit
                            ->required(fn (string $operation) => $operation === 'create')
                            // Hanya kirim data ke function mutate jika field ini diisi
                            ->dehydrated(fn (?string $state) => filled($state)),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->inline(false),
                            
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\DateTimePicker::make('starts_at')
                                ->required()
                                ->label('Waktu Mulai'),
                            Forms\Components\DateTimePicker::make('ends_at')
                                ->required()
                                ->label('Waktu Berakhir')
                                ->after('starts_at'), // Validasi logika waktu
                        ]),
                    ])->columns(2),

                Forms\Components\Section::make('Target & Pembatasan')
                    ->description('Atur siapa yang boleh menggunakan kode ini.')
                    ->schema([
                        Forms\Components\Select::make('prody_id')
                            ->label('Prodi Target')
                            ->required()
                            ->options(function () {
                                $user = auth()->user();
                                if ($user?->hasRole('tutor')) {
                                    return Prody::query()
                                        ->whereIn('id', $user->assignedProdyIds())
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                }
                                return Prody::query()->orderBy('name')->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload(),

                        Forms\Components\Toggle::make('restrict_to_prody')
                            ->label('Wajibkan Prodi Tersebut?')
                            ->helperText('Jika aktif, mahasiswa dari prodi lain akan ditolak.')
                            ->default(true)
                            // Logic disable tutor lebih aman diurus di backend (mutate), 
                            // tapi di UI bisa di-disable visual saja
                            ->disabled(fn() => auth()->user()?->hasRole('tutor'))
                            ->dehydrated(), // Pastikan nilai terkirim meski disabled
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('session.title')
                    ->label('Meeting')
                    ->limit(30)
                    ->searchable()
                    ->description(fn ($record) => $record->quiz ? 'Paket: ' . $record->quiz->title : 'Semua Paket'),

                Tables\Columns\TextColumn::make('code_hint')
                    ->label('Kode')
                    ->badge()
                    ->color('gray')
                    ->copyable() // Bisa dicopy hint-nya (opsional)
                    ->searchable(['code_hint']), // Bisa dicari parsial

                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime('d M Y H:i')
                    ->label('Durasi')
                    ->formatStateUsing(fn ($record) => 
                        $record->starts_at->format('d M H:i') . ' - ' . $record->ends_at->format('d M H:i')
                    )
                    ->sortable(['starts_at']),
                
                Tables\Columns\TextColumn::make('prody.name')
                    ->label('Target Prodi')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('session_id')
                    ->relationship('session', 'title')
                    ->label('Meeting'),
                    
                Tables\Filters\TernaryFilter::make('is_active')->label('Status Aktif'),
                
                Tables\Filters\SelectFilter::make('prody_id')
                    ->label('Prodi')
                    ->relationship('prody', 'name'), 
                    // Relationship filter lebih efisien daripada options query manual jika tidak butuh filtering role yang kompleks di sisi filter list
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('starts_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['prody', 'creator', 'session', 'quiz']);

        $user = auth()->user();

        if ($user && $user->hasRole('tutor')) {
            $ids = $user->assignedProdyIds();
            // Tutor melihat apa yang dia buat ATAU yang ditargetkan ke prodi dia
            return $query->where(function (Builder $q) use ($ids, $user) {
                $q->whereIn('prody_id', $ids)
                  ->orWhere('created_by', $user->id);
            });
        }

        return $query;
    }

    public static function makeCodeHint(string $code): string
    {
        $code = trim($code);
        $len  = mb_strlen($code);

        if ($len <= 4) {
            return mb_substr($code, 0, 1) . '••' . mb_substr($code, -1);
        }

        return mb_substr($code, 0, 2) . '•••' . mb_substr($code, -2);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBasicListeningConnectCodes::route('/'),
            'create' => Pages\CreateBasicListeningConnectCode::route('/create'),
            'edit'   => Pages\EditBasicListeningConnectCode::route('/{record}/edit'),
        ];
    }
}