<?php

namespace App\Docs;

use App\Admin\Screens\Docs\Pages;

/**
 * The documentation pages the panel shows, in reading order. A page is the
 * path of its file under docs/{locale}/ without `.md`; each has a screen class
 * (its slug is `docs-` + the path with `/` → `-`) and a menu entry. Pages
 * outside this list are linked to on GitHub.
 */
final class DocsCatalog
{
    /** @var list<array{page: string, title: string, group: ?string, icon: string, screen: class-string}> */
    public const PAGES = [
        ['page' => 'getting-started', 'title' => 'Getting started', 'group' => null, 'icon' => 'rocket', 'screen' => Pages\GettingStartedPage::class],
        ['page' => 'integration', 'title' => 'Adding to an existing app', 'group' => null, 'icon' => 'plug', 'screen' => Pages\IntegrationPage::class],
        ['page' => 'architecture', 'title' => 'Architecture', 'group' => null, 'icon' => 'layers', 'screen' => Pages\ArchitecturePage::class],
        ['page' => 'concepts/resources', 'title' => 'Resources', 'group' => 'Concepts', 'icon' => 'database', 'screen' => Pages\ConceptsResourcesPage::class],
        ['page' => 'concepts/screens', 'title' => 'Screens', 'group' => 'Concepts', 'icon' => 'monitor', 'screen' => Pages\ConceptsScreensPage::class],
        ['page' => 'concepts/widgets-and-dashboards', 'title' => 'Widgets & dashboards', 'group' => 'Concepts', 'icon' => 'layout-dashboard', 'screen' => Pages\ConceptsWidgetsPage::class],
        ['page' => 'concepts/menu', 'title' => 'Menu', 'group' => 'Concepts', 'icon' => 'list', 'screen' => Pages\ConceptsMenuPage::class],
        ['page' => 'concepts/actions', 'title' => 'Actions', 'group' => 'Concepts', 'icon' => 'zap', 'screen' => Pages\ConceptsActionsPage::class],
        ['page' => 'concepts/permissions', 'title' => 'Permissions', 'group' => 'Concepts', 'icon' => 'shield', 'screen' => Pages\ConceptsPermissionsPage::class],
        ['page' => 'concepts/i18n', 'title' => 'i18n', 'group' => 'Concepts', 'icon' => 'languages', 'screen' => Pages\ConceptsI18nPage::class],
        ['page' => 'concepts/tenancy', 'title' => 'Tenancy', 'group' => 'Concepts', 'icon' => 'building', 'screen' => Pages\ConceptsTenancyPage::class],
        ['page' => 'fields-reference', 'title' => 'Fields reference', 'group' => 'Reference', 'icon' => 'pencil', 'screen' => Pages\FieldsReferencePage::class],
        ['page' => 'layouts-reference', 'title' => 'Layouts reference', 'group' => 'Reference', 'icon' => 'layout', 'screen' => Pages\LayoutsReferencePage::class],
        ['page' => 'api-reference', 'title' => 'API reference', 'group' => 'Reference', 'icon' => 'code', 'screen' => Pages\ApiReferencePage::class],
        ['page' => 'glossary', 'title' => 'Glossary', 'group' => 'Reference', 'icon' => 'book', 'screen' => Pages\GlossaryPage::class],
        ['page' => 'testing', 'title' => 'Testing', 'group' => 'Guides', 'icon' => 'check-circle', 'screen' => Pages\TestingPage::class],
        ['page' => 'frontend-extension', 'title' => 'Frontend extension', 'group' => 'Guides', 'icon' => 'puzzle', 'screen' => Pages\FrontendExtensionPage::class],
        ['page' => 'demo-mode', 'title' => 'Demo mode', 'group' => 'Guides', 'icon' => 'play', 'screen' => Pages\DemoModePage::class],
        ['page' => 'migration-guide', 'title' => 'Migration guide', 'group' => 'Guides', 'icon' => 'history', 'screen' => Pages\MigrationGuidePage::class],
    ];

    /** Section titles of the docs menu, in order; null = top level. */
    public const GROUPS = ['Concepts' => 'boxes', 'Reference' => 'bookmark', 'Guides' => 'map'];

    public static function slugFor(string $page): string
    {
        return 'docs-'.str_replace('/', '-', $page);
    }

    public function has(string $page): bool
    {
        return $this->entry($page) !== null;
    }

    public function title(string $page): ?string
    {
        return $this->entry($page)['title'] ?? null;
    }

    /** @return array{page: string, title: string, group: ?string, icon: string, screen: class-string}|null */
    public function entry(string $page): ?array
    {
        foreach (self::PAGES as $entry) {
            if ($entry['page'] === $page) {
                return $entry;
            }
        }

        return null;
    }

    /** @return list<class-string> */
    public function screens(): array
    {
        return array_column(self::PAGES, 'screen');
    }

    /** Changes when the list changes, so cached link rewrites follow it. */
    public function fingerprint(): string
    {
        return md5(implode(',', array_column(self::PAGES, 'page')));
    }
}
