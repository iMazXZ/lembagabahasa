<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Models\Post;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Semua')
                ->badge(Post::query()->count()),
        ];

        foreach (Post::TYPES as $type => $label) {
            $tabs[$type] = Tab::make($label)
                ->badge(Post::query()->where('type', $type)->count())
                ->query(fn (Builder $query): Builder => $query->where('type', $type));
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function configureCreateAction(CreateAction | Tables\Actions\CreateAction $action): void
    {
        parent::configureCreateAction($action);

        $action
            ->slideOver()
            ->url(null)
            ->modalHeading('Buat Posting Informasi')
            ->modalSubmitActionLabel('Simpan Postingan')
            ->modalWidth('7xl');
    }
}
