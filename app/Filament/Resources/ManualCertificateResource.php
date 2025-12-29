<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManualCertificateResource\Pages;
use App\Models\CertificateCategory;
use App\Models\ManualCertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ManualCertificateResource extends Resource
{
    protected static ?string $model = ManualCertificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Sertifikat';
    protected static ?string $navigationLabel = 'Sertifikat Manual';
    protected static ?string $modelLabel = 'Sertifikat Manual';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Kategori & Semester')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label('Kategori Sertifikat')
                        ->options(CertificateCategory::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn(Set $set) => $set('semester', null)),

                    Forms\Components\Select::make('semester')
                        ->label('Semester')
                        ->options(function (Get $get) {
                            $categoryId = $get('category_id');
                            if (!$categoryId) return [];
                            
                            $category = CertificateCategory::find($categoryId);
                            return $category?->getSemesterOptions() ?? [];
                        })
                        ->visible(function (Get $get) {
                            $categoryId = $get('category_id');
                            if (!$categoryId) return false;
                            
                            $category = CertificateCategory::find($categoryId);
                            return !empty($category?->semesters);
                        }),

                    Forms\Components\DatePicker::make('issued_at')
                        ->label('Tanggal Terbit')
                        ->required()
                        ->default(now()),
                ]),

            Forms\Components\Section::make('Data Peserta')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('srn')
                        ->label('NPM / SRN')
                        ->maxLength(50),

                    Forms\Components\TextInput::make('study_program')
                        ->label('Program Studi')
                        ->maxLength(255)
                        ->placeholder('ENGLISH EDUCATION'),
                ]),

            Forms\Components\Section::make('Nilai')
                ->columns(4)
                ->schema(function (Get $get) {
                    $categoryId = $get('category_id');
                    if (!$categoryId) {
                        return [
                            Forms\Components\Placeholder::make('hint')
                                ->content('Pilih kategori terlebih dahulu untuk melihat field nilai.')
                                ->columnSpanFull(),
                        ];
                    }

                    $category = CertificateCategory::find($categoryId);
                    $scoreFields = $category?->score_fields ?? [];

                    if (empty($scoreFields)) {
                        return [
                            Forms\Components\Placeholder::make('hint')
                                ->content('Kategori ini tidak memiliki field nilai.')
                                ->columnSpanFull(),
                        ];
                    }

                    return collect($scoreFields)->map(function ($field) {
                        return Forms\Components\TextInput::make("scores.{$field}")
                            ->label(ucfirst($field))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100);
                    })->all();
                }),

            Forms\Components\Section::make('Hasil')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('total_score')
                        ->label('Total')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Auto-calculated'),

                    Forms\Components\TextInput::make('average_score')
                        ->label('Rata-rata')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Auto-calculated'),

                    Forms\Components\TextInput::make('grade')
                        ->label('Grade')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Auto-determined'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('certificate_number')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('average_score')
                    ->label('Rata-rata')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('grade')
                    ->label('Grade')
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        str_starts_with($state, 'A') => 'success',
                        str_starts_with($state, 'B') => 'info',
                        str_starts_with($state, 'C') => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(CertificateCategory::pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn(ManualCertificate $record) => route('manual-certificate.download', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManualCertificates::route('/'),
            'create' => Pages\CreateManualCertificate::route('/create'),
            'edit' => Pages\EditManualCertificate::route('/{record}/edit'),
        ];
    }
}
