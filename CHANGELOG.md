# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Entries for releases published before this file existed were reconstructed from
the tagged commit history.

## [Unreleased] — 2.0.0

The demo is rebuilt from scratch as the public showcase and documentation site
of laravel-admin.

### Added
- A fresh Laravel 13 application on laravel-admin ^1.37 with the starter, health,
  jobs, pulse and media packs.
- A demo shop (category tree, products with images, customers, orders with a
  status flow and line items) and a blog (posts in Markdown, categories, tags,
  authors), seeded deterministically with a few thousand records.
- Demo mode: one-click Administrator / Editor / Viewer accounts, the read-only
  guard, a banner with a countdown, and `demo:reset` scheduled hourly.
- The laravel-admin documentation rendered inside the panel, in the panel's
  language, with in-panel links and "Edit on GitHub".
- The showcase convention: every example screen shows its own PHP source.
- The showcase itself, about 50 examples in seven groups: Dashboards (every
  widget type, named widget instances, a dashboard screen with periods,
  polling and gated widgets), Forms (every field type, validation, dependent
  fields, infolists), Grids (focused resources for columns and badges,
  filters and export, row and bulk actions, inline editing, a tree, embedded
  tables, reordering and soft deletes), Layouts, Actions (confirmations,
  modal forms, responses, background jobs, permission-gated buttons),
  Navigation (a nested menu, badges, query parameters) and Notifications
  (toasts, the notification centre, callouts, error states).
- The Russian-only documentation pages: recipes, the HTTP API, the sister
  packs and the contributing guide.
- Landing screenshots in the light and the dark theme.
- A home dashboard with sales KPIs, revenue, order status, best sellers, a
  heatmap and a fulfilment gauge.
- A landing page at `/` (English and Russian, light and dark, no Node build).
- A Playwright crawler that signs in as each role and visits every menu entry.
- A one-container Docker image (FrankenPHP, SQLite, scheduler, queue worker).

### Changed
- On laravel-admin ^1.39 (with laravel-delayed-process ^2.1.2), the showcase
  uses what it fixed instead of working around it: the breadcrumb trail on
  "Where am I", a real progress bar for the background report
  (`ProcessProgressInterface::setProgress()`), the drawer's `footer()`,
  `message_link` in every shape, `ActionFailedException` from a screen method,
  `Select::multiple()` and a checkbox group, `asImage()` in a `RelationTable`,
  `defaultHidden()` and PHP date formats in columns, gauge `precision()` and
  theme tones in gauges and charts, widget captions translated by the panel,
  a relation column in a `RecentListWidget`, and the docs page "Auth".
- The inline-editing example states that `editableForRow()` is enforced on
  the server too.

### Fixed
- The Description and Link columns of the columns example were empty: they
  came from `transformRecord()`, which the list does not use. They are now a
  `format()` over the description and an `asLink()` template.
- The background report failed in the queue worker: named parameters reach a
  delayed-process handler as one array, so the actions pass them in order.
- Revenue chart months follow the panel language.
- Every demo account starts with a few notifications under the bell.

### Removed
- The previous demo application and its debugging scripts.

## [v1.3.0] - 2026-07-20

### Added
- Custom Screens demonstration, a hierarchical menu and a dashboard laid out with row spans.
- `e2e-full-flow.mjs`: a ten-step end-to-end smoke test over the running demo.

### Changed
- Dependencies moved to the canonical stack: laravel-admin ^1.7, sister packages ^1.3, Laravel 13.
- The bundled editor follows @dskripchenko/wysiwyg through ^0.2.7, which adds theme support,
  a source view with syntax highlighting and a resize handle.

## [v1.2.3] - 2026-05-07

### Changed
- @dskripchenko/wysiwyg is the default editor of the demo.

## [v1.2.2] - 2026-05-07

### Changed
- Synchronised with @dskripchenko/laravel-admin 1.2.2 and added the `qrcode-svg` dependency.

## [v1.2.0] - 2026-05-07

### Added
- Dashboard, RBAC roles, loggable models, the Quill editor and every sister package.
- Single-page frontend wired through laravel-admin core 1.1.0.

### Fixed
- Demo resources are registered through `AppServiceProvider`, which is where the panel looks for them.

## [v0.1.0] - 2026-05-01

### Added
- Initial demo skeleton and its Packagist metadata.
