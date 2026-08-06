# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Entries for releases published before this file existed were reconstructed from
the tagged commit history.

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
