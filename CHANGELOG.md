# Changelog

## 1.2.1 - 2026-11-06

### Fixed
- The `|markdown` and `|markdownSafe` Latte filters (and `convertSafe()`) ignored the extensions from the configuration and always used CommonMark plus GFM, so `mark`, `footnote`, `toc`, `smartpunct`, `heading_permalink` and `external_link` had no effect in templates. Safe conversion now builds on the configured extensions and still forces `html_input: strip` and `allow_unsafe_links: false`.

### Changed
- README: the filter returns `Latte\Runtime\Html`, so `|noescape` is not needed. The `phpstan` command is removed from the Development section (PHPStan is not a dependency).

### Added
- Tests for the `mark` extension, for `convertSafe()` with configured extensions and for the Latte filters.
