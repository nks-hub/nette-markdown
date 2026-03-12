# nks-hub/nette-markdown

[![Build Status](https://github.com/nks-hub/nette-markdown/actions/workflows/ci.yml/badge.svg)](https://github.com/nks-hub/nette-markdown/actions)
[![Packagist Version](https://img.shields.io/packagist/v/nks-hub/nette-markdown.svg)](https://packagist.org/packages/nks-hub/nette-markdown)
[![Packagist Downloads](https://img.shields.io/packagist/dt/nks-hub/nette-markdown.svg)](https://packagist.org/packages/nks-hub/nette-markdown)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2+-8892be.svg)](https://php.net/)
[![Tests](https://img.shields.io/badge/tests-10%20passed-brightgreen.svg)](https://github.com/nks-hub/nette-markdown/actions)

> Nette Framework DI extension wrapping [league/commonmark](https://commonmark.thephpleague.com/) with GitHub Flavored Markdown support, Latte filter integration, and configurable extension system.

---

## Why nette-markdown?

Nette Framework lacks a first-class Markdown integration. While `league/commonmark` is an excellent CommonMark parser, wiring it into Nette's DI container, configuring extensions, and registering Latte filters requires boilerplate code in every project.

This package provides a **zero-config drop-in** that just works:

- **Nette DI Extension** — Register in `config.neon`, get `MarkdownConverter` autowired everywhere
- **Latte Filter** — Use `{$content|markdown}` in any template, automatically registered
- **GFM by Default** — Tables, task lists, strikethrough, autolinks out of the box
- **Configurable** — Enable/disable extensions, set security policies, add heading permalinks
- **Safe by Default** — HTML input stripped, unsafe links blocked

---

## Quick Start

### Installation

```bash
composer require nks-hub/nette-markdown
```

### Register in Nette DI

```neon
# config/common.neon
extensions:
    markdown: NksHub\Markdown\DI\MarkdownExtension
```

That's it. `MarkdownConverter` is now available for injection, and the `|markdown` Latte filter is registered automatically.

---

## Usage

### In Presenters / Services (Dependency Injection)

```php
use NksHub\Markdown\MarkdownConverter;

class WikiPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private MarkdownConverter $markdown,
    ) {}

    public function renderView(string $slug): void
    {
        $page = $this->pages->findBySlug($slug);
        $this->template->htmlContent = $this->markdown->convert($page->getContent());
    }
}
```

### In Latte Templates

```latte
{* Filter syntax *}
{$page->getContent()|markdown|noescape}

{* Or in a block *}
{var $html = ($page->getContent()|markdown)}
{$html|noescape}
```

### Standalone (Without Nette DI)

```php
use NksHub\Markdown\MarkdownConverter;

$converter = new MarkdownConverter();
$html = $converter->convert('# Hello **World**');
// <h1>Hello <strong>World</strong></h1>

// Safe mode — strips all HTML, blocks unsafe links
$safeHtml = $converter->convertSafe($userInput);
```

---

## Features

| Feature | Description |
|---------|-------------|
| **CommonMark** | Full CommonMark spec compliance via league/commonmark 2.x |
| **GFM Tables** | Pipe-delimited tables with alignment |
| **Task Lists** | `- [x] Done` / `- [ ] Todo` checkbox rendering |
| **Strikethrough** | `~~deleted~~` to `<del>deleted</del>` |
| **Autolinks** | URLs automatically converted to clickable links |
| **Footnotes** | `[^1]` reference-style footnotes (opt-in) |
| **Smart Punctuation** | Curly quotes, em-dashes, ellipsis (opt-in) |
| **Heading Permalinks** | Anchor links on headings for deep linking (opt-in) |
| **Table of Contents** | Auto-generated TOC from headings (opt-in) |
| **Fenced Code Blocks** | ` ```lang ` syntax with language class output |
| **XSS Protection** | HTML stripped, unsafe links (`javascript:`) blocked by default |
| **Latte Integration** | `\|markdown` filter auto-registered on all Latte engines |

---

## Configuration

### Default (Zero Config)

```neon
extensions:
    markdown: NksHub\Markdown\DI\MarkdownExtension
```

Defaults: CommonMark + GFM (tables, task lists, strikethrough, autolinks), HTML stripped, unsafe links blocked.

### Full Configuration

```neon
extensions:
    markdown: NksHub\Markdown\DI\MarkdownExtension

markdown:
    # Extensions to enable (default: commonmark + gfm)
    extensions:
        - commonmark       # Core CommonMark spec (always needed)
        - gfm              # Tables + Strikethrough + Task Lists + Autolinks
        - footnote         # Reference-style footnotes
        - smartpunct       # Curly quotes, em-dashes, ellipsis
        - heading_permalink # Anchor links on headings
        - toc              # Auto-generated Table of Contents

    # HTML input handling: strip (default), allow, escape
    html_input: strip

    # Block javascript:, vbscript:, data: links (default: false = blocked)
    allow_unsafe_links: false

    # Heading permalink symbol (only when heading_permalink extension enabled)
    heading_permalink:
        symbol: '#'
```

### Available Extensions

| Key | Extension | Description |
|-----|-----------|-------------|
| `commonmark` | `CommonMarkCoreExtension` | Core CommonMark spec — always include this |
| `gfm` | Tables + Strikethrough + TaskList + Autolink | GitHub Flavored Markdown bundle |
| `footnote` | `FootnoteExtension` | `[^1]` reference-style footnotes |
| `smartpunct` | `SmartPunctExtension` | Smart quotes, dashes, ellipsis |
| `heading_permalink` | `HeadingPermalinkExtension` | Clickable anchor links on headings |
| `toc` | `TableOfContentsExtension` | Auto-generated TOC from headings |

---

## API Reference

### `MarkdownConverter`

```php
use NksHub\Markdown\MarkdownConverter;

// Default: CommonMark + GFM, HTML stripped, unsafe links blocked
$converter = new MarkdownConverter();

// Custom config
$converter = new MarkdownConverter([
    'extensions' => ['commonmark', 'gfm', 'footnote'],
    'html_input' => 'allow',
    'allow_unsafe_links' => false,
]);

// Convert Markdown to HTML
$html = $converter->convert($markdown);

// Safe conversion (always strips HTML + blocks unsafe links, regardless of config)
$safeHtml = $converter->convertSafe($untrustedInput);
```

### `MarkdownFilter` (Latte)

Automatically registered when using the DI extension. Callable as a Latte filter:

```latte
{$content|markdown|noescape}
```

Returns `Latte\Runtime\Html` instance, so `|noescape` is required to render the HTML.

### `MarkdownExtension` (Nette DI)

Registers two services:
- `NksHub\Markdown\MarkdownConverter` — injectable converter
- `NksHub\Markdown\Latte\MarkdownFilter` — auto-registered on all Latte engines

---

## Security

The converter is **safe by default**:

| Setting | Default | Effect |
|---------|---------|--------|
| `html_input` | `strip` | Removes all raw HTML from Markdown input |
| `allow_unsafe_links` | `false` | Blocks `javascript:`, `vbscript:`, `data:` URLs |

For user-generated content, use `convertSafe()` which enforces both settings regardless of constructor config.

**Never** use `html_input: allow` with untrusted input — it allows raw HTML injection including `<script>` tags.

---

## Development

```bash
# Install dependencies
composer install

# Run tests (PHPUnit 10)
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/

# Static analysis
vendor/bin/phpstan analyse
```

---

## Requirements

- **PHP**: 8.2+
- **league/commonmark**: ^2.8
- **nette/di**: ^3.1
- **latte/latte**: ^3.0

---

## Contributing

Contributions are welcome! For major changes, please open an issue first.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: description'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Support

- 📧 **Email:** dev@nks-hub.cz
- 🐛 **Bug reports:** [GitHub Issues](https://github.com/nks-hub/nette-markdown/issues)
- 📖 **CommonMark docs:** [commonmark.thephpleague.com](https://commonmark.thephpleague.com/)
- 🏗️ **Nette Framework:** [nette.org](https://nette.org)

## License

MIT License — see [LICENSE](LICENSE) for details.

---

## Links

- [league/commonmark](https://commonmark.thephpleague.com/) — The underlying CommonMark parser
- [Packagist Package](https://packagist.org/packages/nks-hub/nette-markdown)
- [@nks-hub/texy-editor](https://github.com/nks-hub/texy-ts-editor) — TypeScript Texy/Markdown editor with toolbar

---

<p align="center">
  Made with ❤️ by <a href="https://github.com/nks-hub">NKS Hub</a>
</p>
