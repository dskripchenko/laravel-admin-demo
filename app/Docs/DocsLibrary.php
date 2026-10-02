<?php

namespace App\Docs;

use Dskripchenko\LaravelAdmin\Admin;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;

/**
 * Reads the laravel-admin documentation shipped in the Composer package
 * (`vendor/dskripchenko/laravel-admin/docs/{locale}`) and prepares a page for
 * Layout::markdown():
 *
 *  - picks the locale's file, falling back to English when it is missing;
 *  - drops the YAML front matter (its `title` becomes the page title);
 *  - rewrites relative links: a link to another page of the catalog becomes
 *    the relative slug of its screen (`docs-concepts-menu#items`), which the
 *    layout's linkBase turns into an in-panel address; any other relative
 *    link (source files, pages outside the catalog) points at GitHub.
 *
 * The prepared text is cached by file path, size and modification time, so a
 * page costs one stat() call once warm and a package update invalidates it.
 */
final class DocsLibrary
{
    public const FALLBACK_LOCALE = 'en';

    /** Names of the documentation's languages, for the "only in …" note. */
    public const LANGUAGES = ['en' => 'English', 'ru' => 'Russian', 'de' => 'German', 'zh' => 'Chinese'];

    /** @var array<string, array{title: string, markdown: string, locale: string, path: string}> */
    private array $memo = [];

    public function __construct(private readonly DocsCatalog $catalog) {}

    /** The package's docs/ directory. */
    public function root(): string
    {
        $configured = config('demo.docs_path');
        if (is_string($configured) && $configured !== '') {
            return rtrim($configured, '/');
        }

        // src/Admin.php → the package root.
        return dirname((string) (new ReflectionClass(Admin::class))->getFileName(), 2).'/docs';
    }

    /**
     * A page ready for Layout::markdown(). `locale` is the language actually
     * served, `path` the file relative to docs/.
     *
     * @return array{title: string, markdown: string, locale: string, path: string}
     */
    public function page(string $page, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $memoKey = $locale.'|'.$page;

        return $this->memo[$memoKey] ??= $this->load($page, $locale);
    }

    /**
     * The locale whose file exists for the page: the requested one, then
     * English, then any other — some pages are written in Russian only.
     */
    public function resolveLocale(string $page, string $locale): ?string
    {
        $others = array_map('basename', glob($this->root().'/*', GLOB_ONLYDIR) ?: []);
        foreach (array_unique([$locale, self::FALLBACK_LOCALE, ...$others]) as $candidate) {
            if (is_file($this->root()."/{$candidate}/{$page}.md")) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array{title: string, markdown: string, locale: string, path: string}
     */
    private function load(string $page, string $locale): array
    {
        $served = $this->resolveLocale($page, $locale);
        if ($served === null) {
            return [
                'title' => $page,
                'markdown' => "> **Warning** This page is not in the installed package version.\n",
                'locale' => $locale,
                'path' => "{$locale}/{$page}.md",
            ];
        }

        $file = $this->root()."/{$served}/{$page}.md";
        $stat = stat($file) ?: ['size' => 0, 'mtime' => 0];
        // The requested locale is part of the key: a page served in another
        // language carries a note in the requested one.
        $key = 'docs:'.md5($file.'|'.$stat['size'].'|'.$stat['mtime'].'|'.$locale.'|'.$this->catalog->fingerprint());

        return Cache::rememberForever($key, function () use ($file, $page, $served, $locale): array {
            [$title, $body] = $this->splitFrontMatter((string) file_get_contents($file));
            // The screen header shows the title: drop the page's own H1.
            if (preg_match('/^# (.+)\n+/', $body, $h1)) {
                $title ??= trim($h1[1]);
                $body = substr($body, strlen($h1[0]));
            }
            $body = $this->rewriteLinks($body, $served, $page);
            if ($served !== $locale) {
                $note = $served === self::FALLBACK_LOCALE
                    ? __('This page has not been translated yet; the English version is shown.')
                    : __('This page is only available in :language so far.', ['language' => __(self::LANGUAGES[$served] ?? $served)]);
                $body = '> **'.__('Note').'** '.$note."\n\n".$body;
                // The menu's title, in the panel's language.
                $title = $this->catalog->title($page) ?? $title;
            }

            return [
                'title' => $title ?? $this->catalog->title($page) ?? $page,
                'markdown' => $body,
                'locale' => $served,
                'path' => "{$served}/{$page}.md",
            ];
        });
    }

    /** @return array{?string, string} */
    private function splitFrontMatter(string $text): array
    {
        $text = str_replace("\r\n", "\n", $text);
        if (! str_starts_with($text, "---\n")) {
            return [null, $text];
        }
        $end = strpos($text, "\n---\n", 4);
        if ($end === false) {
            return [null, $text];
        }
        $title = null;
        if (preg_match('/^title:\s*(.+)$/m', substr($text, 4, $end - 4), $m)) {
            $title = trim($m[1], " \t\"'");
        }

        return [$title, ltrim(substr($text, $end + 5))];
    }

    /** Rewrites the relative links outside fenced code blocks. */
    public function rewriteLinks(string $markdown, string $locale, string $page): string
    {
        $parts = preg_split('/^(```.*)$/m', $markdown, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$markdown];
        $inFence = false;
        foreach ($parts as $i => $part) {
            if (preg_match('/^```/', $part)) {
                $inFence = ! $inFence;

                continue;
            }
            if (! $inFence) {
                $parts[$i] = preg_replace_callback(
                    '/(?<!!)\[([^\]]*)\]\(([^)\s]+)((?:\s+"[^"]*")?)\)/',
                    fn (array $m) => '['.$m[1].']('.$this->target($m[2], $locale, $page).$m[3].')',
                    $part,
                ) ?? $part;
            }
        }

        return implode('', $parts);
    }

    private function target(string $url, string $locale, string $page): string
    {
        if ($url === '' || preg_match('#^([a-z][a-z0-9+.-]*:|/|\#|\?)#i', $url)) {
            return $url;
        }

        [$path, $anchor] = array_pad(explode('#', $url, 2), 2, null);
        $anchor = $anchor !== null ? '#'.$anchor : '';

        // The link resolved to a path relative to the repository root.
        $resolved = $this->resolve(dirname("docs/{$locale}/{$page}"), $path);
        if ($resolved === null) {
            return $url;
        }

        // A page of the catalog in any language (the English pages link to
        // the Russian-only recipes) opens in the panel.
        if (preg_match('#^docs/[a-z]{2}/(.+)\.md$#', $resolved, $m) && $this->catalog->has($m[1])) {
            return DocsCatalog::slugFor($m[1]).$anchor;
        }

        return $this->github(str_ends_with($resolved, '/') ? 'tree' : 'blob', $resolved).$anchor;
    }

    /** Joins and collapses `..`/`.`; null when the path climbs above the root. */
    private function resolve(string $directory, string $relative): ?string
    {
        $out = [];
        foreach (explode('/', $directory.'/'.$relative) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                if ($out === []) {
                    return null;
                }
                array_pop($out);

                continue;
            }
            $out[] = $segment;
        }

        return implode('/', $out).(str_ends_with($relative, '/') ? '/' : '');
    }

    /** A GitHub URL of a file of the package repository. */
    public function github(string $mode, string $repositoryPath): string
    {
        return rtrim((string) config('demo.docs_repository'), '/')
            .'/'.$mode.'/'.config('demo.docs_branch', 'main').'/'.$repositoryPath;
    }

    /** The "Edit on GitHub" address of a page in the given locale. */
    public function editUrl(string $page, ?string $locale = null): string
    {
        return $this->github('edit', 'docs/'.$this->page($page, $locale)['path']);
    }
}
