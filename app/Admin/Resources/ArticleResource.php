<?php

declare(strict_types=1);

namespace App\Admin\Resources;

use App\Models\Article;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\TextArea;
use Dskripchenko\LaravelAdmin\Field\Wysiwyg;
use Dskripchenko\LaravelAdmin\Filter\InputFilter;
use Dskripchenko\LaravelAdmin\Filter\OptionsFilter;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;

/**
 * Demo Resource — статьи блога.
 *
 * Показывает: WYSIWYG-поле (Quill/Tinymce подключаются плагинами),
 * select-статус, slug, фильтры, search-by-title.
 */
final class ArticleResource extends Resource
{
    public static string $model = Article::class;

    public static string $icon = 'document-text';

    public static ?string $group = 'Контент';

    public static function slug(): string
    {
        return 'articles';
    }

    public static function permission(): string
    {
        return 'admin.content.articles';
    }

    public static function label(): string
    {
        return 'Статьи';
    }

    public function fields(): array
    {
        return [
            Input::make('title')->required()->title('Заголовок'),
            Input::make('slug')->required()->title('Slug')
                ->help('URL-фрагмент, латиница и дефисы'),
            TextArea::make('excerpt')->title('Анонс')->rows(3),
            Wysiwyg::make('body')->title('Текст статьи'),
            Select::make('status')->options([
                'draft' => 'Черновик',
                'review' => 'На review',
                'published' => 'Опубликовано',
                'archived' => 'В архиве',
            ])->required()->title('Статус'),
            Input::make('published_at')->type('datetime-local')->title('Дата публикации'),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('id')->sort()->width('60px'),
            TableColumn::make('title')->sort()->search()->copyable(),
            TableColumn::make('slug')->copyable(),
            TableColumn::make('status')->asBadge([
                'draft' => 'default',
                'review' => 'warning',
                'published' => 'success',
                'archived' => 'danger',
            ]),
            TableColumn::make('published_at')->sort()->asDateTime(),
        ];
    }

    public function filters(): array
    {
        return [
            InputFilter::for('title')->label('Заголовок'),
            OptionsFilter::for('status')->label('Статус')->options([
                'draft' => 'Черновик',
                'review' => 'На review',
                'published' => 'Опубликовано',
                'archived' => 'В архиве',
            ]),
        ];
    }
}
