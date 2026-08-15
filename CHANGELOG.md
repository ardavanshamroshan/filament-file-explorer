# Changelog

All notable changes to this project are documented here.

Version numbering reflects the in-app file explorer evolution in erp.cbiha
before this standalone package (Finder UI, drag selection, ACL, list/explorer
split, and full filemanager merge — five feature cycles).

## 0.5.2 - 2026-08-15

### Changed

- README cover and screenshots marked `filament-hidden` so they are not duplicated on filamentphp.com
- README cover image served from an absolute raw GitHub URL

## 0.5.1 - 2026-08-12

### Added

- `HasFileExplorer` model trait (auto root folder, scope key, `ensureFileExplorerRoot()`)
- `FileExplorerFilesPage` base page for files table
- `HasFileExplorerResource` helpers for page registration + table action
- Generator stubs (`ExplorerPage`, `FilesListPage`, `Authorizer`, `FolderIdMigration`)
- Commands: `make-page`, `make-authorizer`, `make-folder-migration`
- Install `--stubs` / `--migrate`; stubs publishable only on demand

## 0.5.0 - 2026-08-12

First public package release extracted from production ERP usage.

### Added

- Finder-style `FileExplorer` Livewire component (grid, list, table, details)
- Sidebar folder tree, breadcrumbs, back/forward navigation, clipboard cut/copy/paste
- Spatie Media Library `Folder` model with configurable table and legacy morph class
- `FileExplorerAuthorizer` contract and `AllowAllAuthorizer` for demos
- Media download, single-file zip, and folder zip routes
- `FilamentFileExplorerPlugin` for Filament v4 and v5 with compiled CSS/JS assets
- `InteractsWithFileExplorer` trait and `FileExplorerPage` base page
- `InteractsWithFileExplorerTable` for Filament resource file listings
- `FileExplorerPicker` form field with modal explorer
- `filament-file-explorer:install` Artisan command
- Pest tests and GitHub Actions CI matrix (Filament 4 + 5)
- README, docs, and UI preview assets
