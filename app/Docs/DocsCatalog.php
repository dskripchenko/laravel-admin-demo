<?php

namespace App\Docs;

use App\Admin\Screens\Docs\Pages;

/**
 * The documentation pages the panel shows, in reading order. A page is the
 * path of its file under docs/{locale}/ without `.md`; each has a screen class
 * (its slug is `docs-` + the path with `/` → `-`) and a menu entry. Pages
 * outside this list are linked to on GitHub. Some pages (the recipes, the
 * HTTP API, the sister packs) are written in Russian only; they are shown in
 * Russian whatever the panel's language.
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
        ['page' => 'contributing', 'title' => 'Contributing', 'group' => 'Guides', 'icon' => 'users', 'screen' => Pages\ContributingPage::class],
        ['page' => 'recipes/audit-trail', 'title' => 'Audit trail', 'group' => 'Recipes', 'icon' => 'history', 'screen' => Pages\RecipesAuditTrailPage::class],
        ['page' => 'recipes/custom-actions', 'title' => 'Custom actions', 'group' => 'Recipes', 'icon' => 'zap', 'screen' => Pages\RecipesCustomActionsPage::class],
        ['page' => 'recipes/file-uploads', 'title' => 'File uploads', 'group' => 'Recipes', 'icon' => 'upload', 'screen' => Pages\RecipesFileUploadsPage::class],
        ['page' => 'recipes/import-export', 'title' => 'Import and export', 'group' => 'Recipes', 'icon' => 'download', 'screen' => Pages\RecipesImportExportPage::class],
        ['page' => 'recipes/soft-deletes', 'title' => 'Soft deletes', 'group' => 'Recipes', 'icon' => 'trash-2', 'screen' => Pages\RecipesSoftDeletesPage::class],
        ['page' => 'recipes/multi-tenancy', 'title' => 'Multi-tenancy', 'group' => 'Recipes', 'icon' => 'building-2', 'screen' => Pages\RecipesMultiTenancyPage::class],
        ['page' => 'recipes/theming', 'title' => 'Theming', 'group' => 'Recipes', 'icon' => 'palette', 'screen' => Pages\RecipesThemingPage::class],
        ['page' => 'recipes/plugin-development', 'title' => 'Plugin development', 'group' => 'Recipes', 'icon' => 'puzzle', 'screen' => Pages\RecipesPluginDevelopmentPage::class],
        ['page' => 'api/README', 'title' => 'HTTP API overview', 'group' => 'HTTP API', 'icon' => 'book-open', 'screen' => Pages\ApiOverviewPage::class],
        ['page' => 'api/conventions', 'title' => 'Conventions', 'group' => 'HTTP API', 'icon' => 'list-checks', 'screen' => Pages\ApiConventionsPage::class],
        ['page' => 'api/schemas', 'title' => 'Response schemas', 'group' => 'HTTP API', 'icon' => 'file-code', 'screen' => Pages\ApiSchemasPage::class],
        ['page' => 'api/registration', 'title' => 'Registration', 'group' => 'HTTP API', 'icon' => 'plug', 'screen' => Pages\ApiRegistrationPage::class],
        ['page' => 'api/auth', 'title' => 'Auth', 'group' => 'HTTP API', 'icon' => 'lock', 'screen' => Pages\ApiAuthPage::class],
        ['page' => 'api/profile', 'title' => 'Profile', 'group' => 'HTTP API', 'icon' => 'user-circle', 'screen' => Pages\ApiProfilePage::class],
        ['page' => 'api/system', 'title' => 'System', 'group' => 'HTTP API', 'icon' => 'server', 'screen' => Pages\ApiSystemPage::class],
        ['page' => 'api/resources', 'title' => 'Resources', 'group' => 'HTTP API', 'icon' => 'database', 'screen' => Pages\ApiResourcesPage::class],
        ['page' => 'api/actions', 'title' => 'Actions', 'group' => 'HTTP API', 'icon' => 'zap', 'screen' => Pages\ApiActionsPage::class],
        ['page' => 'api/screens', 'title' => 'Screens', 'group' => 'HTTP API', 'icon' => 'monitor', 'screen' => Pages\ApiScreensPage::class],
        ['page' => 'api/dashboards', 'title' => 'Dashboards', 'group' => 'HTTP API', 'icon' => 'layout-dashboard', 'screen' => Pages\ApiDashboardsPage::class],
        ['page' => 'api/delayed', 'title' => 'Delayed processes', 'group' => 'HTTP API', 'icon' => 'clock', 'screen' => Pages\ApiDelayedPage::class],
        ['page' => 'api/exports-imports', 'title' => 'Exports and imports', 'group' => 'HTTP API', 'icon' => 'download', 'screen' => Pages\ApiExportsImportsPage::class],
        ['page' => 'api/uploads', 'title' => 'Uploads', 'group' => 'HTTP API', 'icon' => 'upload', 'screen' => Pages\ApiUploadsPage::class],
        ['page' => 'api/search', 'title' => 'Search', 'group' => 'HTTP API', 'icon' => 'search', 'screen' => Pages\ApiSearchPage::class],
        ['page' => 'api/settings', 'title' => 'Settings', 'group' => 'HTTP API', 'icon' => 'settings', 'screen' => Pages\ApiSettingsPage::class],
        ['page' => 'api/health', 'title' => 'Health', 'group' => 'HTTP API', 'icon' => 'health-check', 'screen' => Pages\ApiHealthPage::class],
        ['page' => 'sister-packs/README', 'title' => 'Sister packs overview', 'group' => 'Sister packs', 'icon' => 'boxes', 'screen' => Pages\PacksOverviewPage::class],
        ['page' => 'sister-packs/starter', 'title' => 'Starter', 'group' => 'Sister packs', 'icon' => 'rocket', 'screen' => Pages\PacksStarterPage::class],
        ['page' => 'sister-packs/media', 'title' => 'Media', 'group' => 'Sister packs', 'icon' => 'images', 'screen' => Pages\PacksMediaPage::class],
        ['page' => 'sister-packs/health', 'title' => 'Health', 'group' => 'Sister packs', 'icon' => 'health-check', 'screen' => Pages\PacksHealthPage::class],
        ['page' => 'sister-packs/jobs', 'title' => 'Jobs', 'group' => 'Sister packs', 'icon' => 'list-checks', 'screen' => Pages\PacksJobsPage::class],
        ['page' => 'sister-packs/pulse', 'title' => 'Pulse', 'group' => 'Sister packs', 'icon' => 'activity', 'screen' => Pages\PacksPulsePage::class],
        ['page' => 'sister-packs/search', 'title' => 'Search', 'group' => 'Sister packs', 'icon' => 'search', 'screen' => Pages\PacksSearchPage::class],
        ['page' => 'sister-packs/quill', 'title' => 'Quill', 'group' => 'Sister packs', 'icon' => 'pencil', 'screen' => Pages\PacksQuillPage::class],
        ['page' => 'sister-packs/tinymce', 'title' => 'TinyMCE', 'group' => 'Sister packs', 'icon' => 'pencil', 'screen' => Pages\PacksTinymcePage::class],
    ];

    /** Section titles of the docs menu, in order; null = top level. */
    public const GROUPS = [
        'Concepts' => 'boxes',
        'Reference' => 'bookmark',
        'Guides' => 'map',
        'Recipes' => 'list-checks',
        'HTTP API' => 'webhook',
        'Sister packs' => 'package',
    ];

    public static function slugFor(string $page): string
    {
        return 'docs-'.strtolower(str_replace('/', '-', $page));
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
