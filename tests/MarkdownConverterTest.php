<?php

declare(strict_types=1);

namespace NksHub\Markdown\Tests;

use NksHub\Markdown\MarkdownConverter;
use PHPUnit\Framework\TestCase;

class MarkdownConverterTest extends TestCase
{
    private MarkdownConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new MarkdownConverter();
    }

    public function testBasicMarkdown(): void
    {
        $html = $this->converter->convert("# Heading\n\n**bold** *italic* [link](https://example.com)");

        $this->assertStringContainsString('<h1>Heading</h1>', $html);
        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('<em>italic</em>', $html);
        $this->assertStringContainsString('<a href="https://example.com">link</a>', $html);
    }

    public function testGfmTables(): void
    {
        $markdown = "| Col A | Col B |\n|-------|-------|\n| foo   | bar   |";
        $html = $this->converter->convert($markdown);

        $this->assertStringContainsString('<table>', $html);
        $this->assertStringContainsString('<th>Col A</th>', $html);
        $this->assertStringContainsString('<td>foo</td>', $html);
    }

    public function testGfmTaskLists(): void
    {
        $markdown = "- [x] Done\n- [ ] Todo";
        $html = $this->converter->convert($markdown);

        $this->assertStringContainsString('checked', $html);
        $this->assertStringContainsString('type="checkbox"', $html);
    }

    public function testGfmStrikethrough(): void
    {
        $html = $this->converter->convert('~~deleted~~');

        $this->assertStringContainsString('<del>deleted</del>', $html);
    }

    public function testGfmAutolinks(): void
    {
        $html = $this->converter->convert('Visit https://example.com for info.');

        $this->assertStringContainsString('<a href="https://example.com">', $html);
    }

    public function testFencedCodeBlocks(): void
    {
        $markdown = "```php\n<?php echo 'hello';\n```";
        $html = $this->converter->convert($markdown);

        $this->assertStringContainsString('class="language-php"', $html);
        $this->assertStringContainsString('<code', $html);
    }

    public function testHtmlStripping(): void
    {
        $html = $this->converter->convert("<script>alert(\"xss\")</script>\n\nSafe text");

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('alert(', $html);
        $this->assertStringContainsString('Safe text', $html);
    }

    public function testUnsafeLinks(): void
    {
        $html = $this->converter->convert('[click me](javascript:alert(1))');

        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function testFootnotes(): void
    {
        $converter = new MarkdownConverter([
            'extensions' => ['commonmark', 'gfm', 'footnote'],
        ]);

        $markdown = "Text with footnote.[^1]\n\n[^1]: Footnote content.";
        $html = $converter->convert($markdown);

        $this->assertStringContainsString('footnote', strtolower($html));
        $this->assertStringContainsString('Footnote content.', $html);
    }

    public function testHeadingPermalinks(): void
    {
        $converter = new MarkdownConverter([
            'extensions' => ['commonmark', 'gfm', 'heading_permalink'],
            'heading_permalink' => ['symbol' => '#'],
        ]);

        $html = $converter->convert('## My Section');

        $this->assertStringContainsString('id="', $html);
        $this->assertStringContainsString('<h2', $html);
    }
}
