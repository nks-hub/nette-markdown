# Nette Markdown

[![CI](https://github.com/nks-hub/nette-markdown/actions/workflows/ci.yml/badge.svg)](https://github.com/nks-hub/nette-markdown/actions/workflows/ci.yml)
[![Packagist Version](https://img.shields.io/packagist/v/nks-hub/nette-markdown.svg)](https://packagist.org/packages/nks-hub/nette-markdown)
[![Packagist Downloads](https://img.shields.io/packagist/dt/nks-hub/nette-markdown.svg)](https://packagist.org/packages/nks-hub/nette-markdown)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://php.net/)
[![Nette Version](https://img.shields.io/badge/nette%2Fdi-%5E3.1-blue.svg)](https://nette.org/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

> Nette DI extension that registers a [league/commonmark](https://commonmark.thephpleague.com/) converter as a service and adds `|markdown` filters to every Latte engine.

## Features

- 🧩 **Nette DI extension** - One line in `config.neon` makes `MarkdownConverter` autowirable
- 🎨 **Latte filter** - `{$content|markdown}` works in any template; the filter is registered on all Latte engines
- 📝 **CommonMark** - Parsing done by league/commonmark 2.x
- 🐙 **GFM by default** - Tables with alignment, task lists (`- [x] Done`), strikethrough (`~~deleted~~`) and autolinks enabled without configuration
- 🔗 **Heading permalinks and TOC** - Anchor links on headings and a generated table of contents (opt-in)
- 📚 **Footnotes and smart punctuation** - `[^1]` reference-style footnotes, curly quotes, dashes, ellipsis (opt-in)
- 🖍️ **Highlight** - `==text==` rendered as `<mark>` (opt-in)
- 💻 **Fenced code blocks** - ` ```lang ` blocks get a `language-*` class
- 🛡️ **Safe defaults** - Raw HTML input stripped, unsafe links (`javascript:`) blocked
- ⚙️ **Configuration** - Pick extensions, set `html_input` and `allow_unsafe_links`, change the permalink symbol

## Requirements

- PHP 8.2 or higher
- league/commonmark ^2.8
- nette/di ^3.1
- latte/latte ^3.0

## Installation

```bash
composer require nks-hub/nette-markdown
```

Register the extension in Nette DI:

```neon
# config/common.neon
extensions:
    markdown: NksHub\Markdown\DI\MarkdownExtension
```

`MarkdownConverter` is then available for injection and the `|markdown` Latte filter is registered.

## Usage

### In presenters and services

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

### In Latte templates

```latte
{* Filter syntax *}
{$page->getContent()|markdown|noescape}

{* Or in a block *}
{var $html = ($page->getContent()|markdown)}
{$html|noescape}
```

### Standalone, without Nette DI

```php
use NksHub\Markdown\MarkdownConverter;

$converter = new MarkdownConverter();
$html = $converter->convert('# Hello **World**');
// <h1>Hello <strong>World</strong></h1>

// Safe mode — strips all HTML, blocks unsafe links
$safeHtml = $converter->convertSafe($userInput);
```

## Configuration

### Defaults

```neon
extensions:
    markdown: NksHub\Markdown\DI\MarkdownExtension
```

Defaults: CommonMark + GFM (tables, task lists, strikethrough, autolinks), HTML stripped, unsafe links blocked.

### Full configuration

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

### Available extensions

| Key | Extension | Description |
|-----|-----------|-------------|
| `commonmark` | `CommonMarkCoreExtension` | Core CommonMark spec, always include this |
| `gfm` | Tables + Strikethrough + TaskList + Autolink | GitHub Flavored Markdown bundle |
| `footnote` | `FootnoteExtension` | `[^1]` reference-style footnotes |
| `smartpunct` | `SmartPunctExtension` | Smart quotes, dashes, ellipsis |
| `heading_permalink` | `HeadingPermalinkExtension` | Clickable anchor links on headings |
| `toc` | `TableOfContentsExtension` | Auto-generated TOC from headings |
| `mark` | `MarkExtension` | `==text==` rendered as `<mark>text</mark>` |
| `external_link` | `ExternalLinkExtension` | Attributes for external links |

## API reference

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

Registered by the DI extension:

```latte
{$content|markdown|noescape}
```

Returns `Latte\Runtime\Html` instance, so `|noescape` is required to render the HTML.

### `MarkdownExtension` (Nette DI)

Filters registered on every Latte engine:
- `|markdown` and `|markdownSafe`: always use safe conversion (`convertSafe()`)
- `|markdownRaw`: uses the converter as configured (respects `html_input`)

Registers two services:
- `NksHub\Markdown\MarkdownConverter`: injectable converter
- `NksHub\Markdown\Latte\MarkdownFilter`: registered on all Latte engines

## Security

Defaults:

| Setting | Default | Effect |
|---------|---------|--------|
| `html_input` | `strip` | Removes all raw HTML from Markdown input |
| `allow_unsafe_links` | `false` | Blocks `javascript:`, `vbscript:`, `data:` URLs |

For user-generated content, use `convertSafe()` which enforces both settings regardless of constructor config.

Do not use `html_input: allow` with untrusted input: it passes raw HTML through, `<script>` tags included.

## Links

- [league/commonmark](https://commonmark.thephpleague.com/): the CommonMark parser used here
- [Packagist package](https://packagist.org/packages/nks-hub/nette-markdown)
- [@nks-hub/texy-editor](https://github.com/nks-hub/texy-ts-editor): TypeScript Texy/Markdown editor with a toolbar

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

<p align="center">
  Made with ❤️ by <a href="https://github.com/nks-hub">NKS Hub</a>
</p>
