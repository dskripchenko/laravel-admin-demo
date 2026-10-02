<?php

namespace App\Http\Controllers;

use Composer\InstalledVersions;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The public front page. English or Russian: `?lang=` wins, then the
 * browser's Accept-Language; the strings are translated through lang/ru.json
 * like the rest of the stand.
 */
final class LandingController
{
    public const LOCALES = ['en', 'ru'];

    public function __invoke(Request $request): View
    {
        $lang = $request->query('lang');
        $locale = is_string($lang) && in_array($lang, self::LOCALES, true)
            ? $lang
            : ($request->getPreferredLanguage(self::LOCALES) ?? 'en');
        app()->setLocale($locale);

        return view('landing', [
            'locale' => $locale,
            'version' => ltrim((string) InstalledVersions::getPrettyVersion('dskripchenko/laravel-admin'), 'v'),
            'adminPath' => '/'.trim((string) config('admin.path', 'admin'), '/'),
            'accounts' => config('demo.accounts'),
        ]);
    }
}
