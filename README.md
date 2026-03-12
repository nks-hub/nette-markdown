# nks-hub/nette-markdown

Nette DI extension that wraps [league/commonmark](https://commonmark.thephpleague.com/) with GitHub Flavored Markdown support and a Latte `|markdown` filter.

## Installation

```bash
composer require nks-hub/nette-markdown
```

## Configuration (config.neon)

```neon
extensions:
    markdown: NksHub\Markdown\DI\MarkdownExtension

markdown:
    extensions: [commonmark, gfm]   # commonmark, gfm, footnote, smartpunct, heading_permalink, toc
    html_input: strip                # strip | allow | escape
    allow_unsafe_links: false
    heading_permalink:
        symbol: '#'
```

## Latte usage

```latte
{* Render markdown content *}
{$content|markdown}

{* Inline block *}
{'## Hello World'|markdown}
```

## Direct service usage

```php
use NksHub\Markdown\MarkdownConverter;

$converter = new MarkdownConverter([
    'extensions' => ['commonmark', 'gfm', 'footnote'],
]);

$html = $converter->convert('# Hello **world**');

// Always safe, strips HTML and unsafe links regardless of config
$safeHtml = $converter->convertSafe($untrustedInput);
```

## Available extensions

| Key | Extension |
|-----|-----------|
| `commonmark` | CommonMarkCoreExtension (headings, links, code, etc.) |
| `gfm` | GFM bundle: TableExtension + StrikethroughExtension + TaskListExtension + AutolinkExtension |
| `footnote` | FootnoteExtension |
| `smartpunct` | SmartPunctExtension (smart quotes, dashes) |
| `heading_permalink` | HeadingPermalinkExtension (anchor links on headings) |
| `toc` | TableOfContentsExtension |

## Security defaults

- `html_input: strip` — raw HTML in markdown is stripped
- `allow_unsafe_links: false` — `javascript:` and `vbscript:` links are removed
- `convertSafe()` always enforces these regardless of instance config
