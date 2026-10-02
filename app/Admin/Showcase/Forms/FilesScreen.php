<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseScreen;
use App\Models\Shop\Product;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\FileUpload;
use Dskripchenko\LaravelAdmin\Field\ImageCropper;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdminMedia\Fields\MediaPicker;

/**
 * Forms › Files and images: a file upload, an image with a preview, an image
 * cropped in the browser, and files picked from the media library. Uploads go
 * through the panel's uploads endpoint; the demo caps them at 2 MB.
 */
final class FilesScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-files';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public static function icon(): string
    {
        return 'image';
    }

    public function name(): string
    {
        return 'Files and images';
    }

    public function description(): ?string
    {
        return 'FileUpload, an image upload, ImageCropper and the media library picker.';
    }

    public function query(mixed ...$params): array
    {
        // Pictures of a product of the demo shop, already in the media library.
        $product = Product::query()->whereNotNull('cover_id')->whereNotNull('gallery')->orderBy('id')->first();

        return [
            'contract' => null,
            'avatar' => null,
            'hero' => null,
            'cover_id' => $product?->cover_id,
            'gallery' => array_slice((array) ($product?->gallery ?? []), 0, 3),
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::block('Uploads', [
                    Layout::rows([
                        FileUpload::make('contract')->accept(['application/pdf', '.docx'])->maxSize(2048)
                            ->help('PDF or DOCX, up to 2 MB'),
                        FileUpload::make('avatar')->image()->maxSize(1024)->help('An image, with a preview'),
                        ImageCropper::make('hero')->title('Hero image')->aspectRatio(16 / 9)->minCrop(320, 180)
                            ->outputSize(1280, 720)->quality(0.85)->help('Cropped to 16:9 in the browser before the upload'),
                    ]),
                ])->icon('upload'),
                Layout::block('The media library', [
                    Layout::rows([
                        MediaPicker::make('cover_id')->title('Cover')->images()->collection('products')->responsiveSet('product'),
                        MediaPicker::make('gallery')->images()->collection('products')->multiple()->maxItems(6)
                            ->help('Up to six pictures; drag to reorder'),
                    ]),
                ])->icon('images'),
            ]),
        ];
    }

    public function commandBar(): array
    {
        return [Button::make('Submit')->method('submit')->primary()->icon('send')];
    }

    /** @param array<string, mixed> $state */
    public function submit(array $state): array
    {
        validator($state, [
            'contract' => 'nullable|array',
            'contract.mime' => 'nullable|in:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'avatar' => 'nullable|array',
            'gallery' => 'array|max:6',
        ], [
            'contract.mime.in' => __('The contract must be a PDF or a DOCX file.'),
        ])->validate();

        $files = count(array_filter([$state['contract'] ?? null, $state['avatar'] ?? null, $state['hero'] ?? null]));

        return ['message' => __('Valid! :files uploaded file(s), :media picked from the library.', [
            'files' => $files,
            'media' => count(array_filter([$state['cover_id'] ?? null])) + count((array) ($state['gallery'] ?? [])),
        ])];
    }
}
