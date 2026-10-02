<?php

namespace App\Admin\Resources\Blog;

use App\Models\Blog\Author;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Filter\SwitcherFilter;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Dskripchenko\LaravelAdminMedia\Fields\MediaPicker;
use Illuminate\Database\Eloquent\Builder;

final class AuthorResource extends Resource
{
    public static string $model = Author::class;

    public static string $icon = 'pencil';

    public static function slug(): string
    {
        return 'authors';
    }

    public static function label(): string
    {
        return 'Authors';
    }

    public function fields(): array
    {
        return [
            Input::make('name')->required()->span(6),
            Input::make('email')->type('email')->required()->span(6),
            Input::make('title')->title('Job title')->span(6),
            Input::make('website')->type('url')->span(6),
            MediaPicker::make('avatar_id')->title('Avatar')->images()->collection('avatars')->responsiveSet('avatar'),
            Textarea::make('bio')->rows(4),
            Switcher::make('is_active')->title('Active')->default(true),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('name')->sort()->search(),
            TableColumn::make('email')->search(),
            TableColumn::make('title')->label('Job title'),
            TableColumn::make('posts_count')->label('Posts')->align('right')->sort(),
            TableColumn::make('is_active')->label('Active')->asBoolean(),
        ];
    }

    public function filters(): array
    {
        return [SwitcherFilter::for('is_active')->label('Active')];
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->withCount('posts');
    }
}
