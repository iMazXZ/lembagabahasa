<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BasicListeningConnectCodeResource\Pages;
use App\Models\BasicListeningConnectCode;
use App\Models\BasicListeningQuiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                Forms\Components\Select::make('session_id')
                    ->relationship('session', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Session'),

                Forms\Components\Select::make('quiz_id')
                    ->label('Quiz (opsional)')
                    ->options(function (callable $get) {
                        $sessionId = $get('session_id');
                        if (!$sessionId) {
                            return [];
                        }
                        return BasicListeningQuiz::where('session_id', $sessionId)
                            ->orderBy('title')
                            ->pluck('title', 'id');
                    })
                    ->reactive()
                    ->helperText('Pilih quiz tertentu untuk kode ini. Kosongkan bila ingin pakai quiz default di session.'),

                Forms\Components\TextInput::make('plain_code')
                    ->label('Plain Code (akan di-hash)')
                    ->helperText('Masukkan kode yang dibagikan ke peserta. Disimpan sebagai hash + hint.')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    // ->dehydrated(false)
                    ->required(fn (string $context) => $context === 'create'),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\DateTimePicker::make('starts_at')
                        ->required()
                        ->label('Mulai'),
                    Forms\Components\DateTimePicker::make('ends_at')
                        ->required()
                        ->label('Berakhir')
                        ->after('starts_at'),
                ]),

                Forms\Components\TextInput::make('max_uses')
                    ->numeric()
                    ->minValue(1)
                    ->label('Batas pakai')
                    ->helperText('Kosongkan untuk tak terbatas.')
                    ->nullable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

                Forms\Components\Textarea::make('rules')
                    ->label('Rules (JSON opsional)')
                    ->placeholder('{"prodi": ["TI","PGSD"]}')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->extraAttributes(['autocomplete' => 'off']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('session.number')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('session.title')->label('Session')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('quiz.title')->label('Quiz')->limit(32)->toggleable(),
                Tables\Columns\TextColumn::make('code_hint')
                    ->label('Hint Kode')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('starts_at')->dateTime('d M Y H:i')->label('Mulai')->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime('d M Y H:i')->label('Berakhir')->sortable(),
                Tables\Columns\TextColumn::make('max_uses')->label('Batas')->placeholder('∞')->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Dibuat')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('session_id')
                    ->relationship('session', 'title')
                    ->label('Session'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('starts_at', 'desc');
    }

    /**
     * Buat hint dari plaintext code (tanpa menyimpan plaintext).
     * Contoh:
     * - "AB12"   -> "A••2"
     * - "LB-2025"-> "LB•••25"
     */
    private static function makeCodeHint(string $code): string
    {
        $code = trim($code);
        $len  = mb_strlen($code);

        if ($len <= 4) {
            return mb_substr($code, 0, 1) . '••' . mb_substr($code, -1);
        }

        return mb_substr($code, 0, 2) . '•••' . mb_substr($code, -2);
    }

    /** Hash & hint sebelum create */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Ambil plaintext dari request (karena field tidak didehydrate)
        $plain = request()->input('data.plain_code') ?? ($data['plain_code'] ?? null);
        if ($plain) {
            $plain = trim($plain);
            $data['code_hash'] = hash('sha256', $plain);
            $data['code_hint'] = self::makeCodeHint($plain);
        }

        // Parse rules jika string JSON
        if (!empty($data['rules']) && is_string($data['rules'])) {
            $decoded = json_decode($data['rules'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['rules'] = $decoded;
            }
        }

        unset($data['plain_code']); // pastikan tidak ikut tersimpan
        return $data;
    }

    /** Hash & hint saat update (jika admin isi plain code baru) */
    public static function mutateFormDataBeforeSave(array $data): array
    {
        $plain = request()->input('data.plain_code') ?? ($data['plain_code'] ?? null);
        if ($plain) {
            $plain = trim($plain);
            $data['code_hash'] = hash('sha256', $plain);
            $data['code_hint'] = self::makeCodeHint($plain);
        }

        if (!empty($data['rules']) && is_string($data['rules'])) {
            $decoded = json_decode($data['rules'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['rules'] = $decoded;
            }
        }

        unset($data['plain_code']);
        return $data;
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
