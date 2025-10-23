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
use Illuminate\Database\Eloquent\Builder;
use App\Models\Prody;

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
                
                Forms\Components\Section::make('Target & Pembatasan')
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
                            ->preload()
                            ->helperText('Kode hanya bisa dipakai oleh mahasiswa prodi ini bila pembatasan aktif.'),

                        Forms\Components\Toggle::make('restrict_to_prody')
                            ->label('Batasi penggunaan ke prodi ini')
                            ->default(true)
                            ->disabled(fn() => auth()->user()?->hasRole('tutor')) // tutor tidak bisa mengubah
                            ->dehydrated(),
                    ])
                    ->columns(2),
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
                Tables\Filters\SelectFilter::make('prody_id')
                    ->label('Prodi')
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
                    ->attribute('prody_id'),

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

   /** Hash & hint + set created_by + default restrict_to_prody (saat CREATE) */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // 1) Set pembuat & default pembatasan prodi
        $data['created_by'] = auth()->id();
        $data['restrict_to_prody'] = $data['restrict_to_prody'] ?? true;

        if (auth()->user()?->hasRole('tutor')) {
            $data['restrict_to_prody'] = true;
        }
        // 2) Tutor hanya boleh memilih prodi yang dia ampu
        if (auth()->user()?->hasRole('tutor')) {
            $allowed = auth()->user()->assignedProdyIds(); // dari User::assignedProdyIds()
            if (empty($allowed)) {
                abort(403, 'Anda belum ditetapkan mengampu prodi mana pun.');
            }
            // Validasi prody_id wajib & termasuk daftar allowed
            validator($data, [
                'prody_id' => 'required|in:' . implode(',', $allowed),
            ], [
                'prody_id.in' => 'Prodi yang dipilih tidak termasuk prodi yang Anda ampu.',
            ])->validate();
        }

        // 3) Hash plain_code -> code_hash + code_hint (tetap seperti punyamu)
        $plain = request()->input('data.plain_code') ?? ($data['plain_code'] ?? null);
        if ($plain) {
            $plain = trim($plain);
            $data['code_hash'] = hash('sha256', $plain);
            $data['code_hint'] = self::makeCodeHint($plain);
        }

        // 4) Parse rules jika string JSON (tetap seperti punyamu)
        if (!empty($data['rules']) && is_string($data['rules'])) {
            $decoded = json_decode($data['rules'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['rules'] = $decoded;
            }
        }

        unset($data['plain_code']); // jangan simpan plaintext
        return $data;
    }

    /** Hash & hint saat UPDATE (jangan ubah created_by) */
    public static function mutateFormDataBeforeSave(array $data): array
    {
        // 1) Jangan izinkan mengubah created_by di update
        unset($data['created_by']);

        if (auth()->user()?->hasRole('tutor')) {
            $data['restrict_to_prody'] = true;
        }

        // 2) Tutor tidak boleh mengganti prodi_id ke prodi yang tidak dia ampu
        if (auth()->user()?->hasRole('tutor') && array_key_exists('prody_id', $data)) {
            $allowed = auth()->user()->assignedProdyIds();
            if (!empty($allowed)) {
                validator($data, [
                    'prody_id' => 'in:' . implode(',', $allowed),
                ], [
                    'prody_id.in' => 'Prodi yang dipilih tidak termasuk prodi yang Anda ampu.',
                ])->validate();
            } else {
                // kalau tutor tidak punya prodi, cegah perubahan
                unset($data['prody_id']);
            }
        }

        // 3) Perbarui hash kalau admin/tutor mengisi plain_code baru
        $plain = request()->input('data.plain_code') ?? ($data['plain_code'] ?? null);
        if ($plain) {
            $plain = trim($plain);
            $data['code_hash'] = hash('sha256', $plain);
            $data['code_hint'] = self::makeCodeHint($plain);
        }

        // 4) Parse rules jika string JSON
        if (!empty($data['rules']) && is_string($data['rules'])) {
            $decoded = json_decode($data['rules'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['rules'] = $decoded;
            }
        }

        unset($data['plain_code']);
        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['prody', 'creator']);

        $user = auth()->user();

        // Admin: semua
        if ($user && $user->hasRole('admin')) {
            return $query;
        }

        // Tutor: hanya prodi yang dia ampu ATAU code yang dia buat
        if ($user && $user->hasRole('tutor')) {
            $ids = $user->assignedProdyIds();
            if (empty($ids)) {
                return $query->where('created_by', $user->id);
            }

            return $query->where(function (Builder $q) use ($ids, $user) {
                $q->whereIn('prody_id', $ids)
                ->orWhere('created_by', $user->id);
            });
        }

        // Peran lain: kosong
        return $query->whereRaw('1=0');
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
