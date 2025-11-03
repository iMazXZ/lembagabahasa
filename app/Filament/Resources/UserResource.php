<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Prody;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\Page;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Get;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-s-users';

    protected static ?string $navigationLabel = 'Manajemen User';

    public static ?string $label = 'Manajemen User';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'Wajib Diisi',
                    ])
                    ->dehydrateStateUsing(fn (string $state): string => ucwords(strtolower($state))),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'Wajib Diisi',
                    ]),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (Page $livewire): bool => $livewire instanceof CreateRecord)
                    ->maxLength(255),
                Forms\Components\TextInput::make('srn')
                    ->label('SRN / NPM')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('prody_id')
                    ->label('Program Studi')
                    ->relationship('prody', 'name')
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Wajib Diisi',
                    ]),
                Forms\Components\TextInput::make('year')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('nilaibasiclistening')
                    ->label('Nilai Basic Listening')
                    ->numeric()
                    ->default(null),

                Forms\Components\Select::make('roles')
                    ->label('Roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple()
                    ->searchable(),

                Forms\Components\FileUpload::make('image')
                    ->label('Foto Profil')
                    ->image()
                    ->default(null)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Tugas Tutor')
                    ->description('Atur prodi yang diampu oleh tutor.')
                    ->schema([
                        Forms\Components\Select::make('tutorProdies')
                            ->label('Prodi yang Diampu')
                            ->relationship('tutorProdies', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Tutor bisa mengampu lebih dari satu prodi, dan satu prodi bisa diampu banyak tutor.')
                            ->visible(fn () => auth()->user()?->hasRole('Admin') === true),
                    ])
                    ->visible(fn () => auth()->user()?->hasRole('Admin') === true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->copyable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('srn')
                    ->label('SRN / NPM')
                    ->copyable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('prody.name')
                    ->label('Program Studi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nilaibasiclistening')
                    ->label('Score BL')
                    ->numeric(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto Profil'),
                Tables\Columns\BadgeColumn::make('roles.name')
                    ->label('Role')
                    ->colors([
                        'gray' => 'pendaftar',
                        'success' => 'Admin',
                        'warning' => 'Staf Administrasi',
                        'info' => 'Penerjemah',
                        'danger' => 'Kepala Lembaga',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('assignRole')
                        ->label('Terapkan Role')
                        ->icon('heroicon-o-user-plus')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Select::make('role_id')
                                ->label('Pilih Role')
                                ->options(fn () => Role::query()->orderBy('name')->pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $role = Role::find($data['role_id'] ?? null);
                            if (! $role) {
                                Notification::make()
                                    ->title('Role tidak ditemukan.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            foreach ($records as $user) {
                                /** @var \App\Models\User $user */
                                $user->syncRoles([$role->name]); // pastikan hanya 1 role
                            }

                            Notification::make()
                                ->title('Role telah diset untuk pengguna terpilih.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => auth()->user()?->hasRole('Admin') === true),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah User';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
