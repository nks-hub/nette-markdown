<?php

declare(strict_types=1);

namespace NksHub\Markdown\Tests;

use Latte\Engine;
use Latte\Loaders\StringLoader;
use NksHub\Markdown\Latte\MarkdownFilter;
use NksHub\Markdown\MarkdownConverter;
use PHPUnit\Framework\TestCase;

class MarkExtensionTest extends TestCase
{
    private const EXTENSIONS = ['commonmark', 'gfm', 'mark', 'footnote'];

    public function testMarkRendersHighlight(): void
    {
        $converter = new MarkdownConverter(['extensions' => self::EXTENSIONS]);

        $this->assertStringContainsString(
            '<p>a <mark>highlighted</mark> word</p>',
            $converter->convert('a ==highlighted== word'),
        );
    }

    public function testMarkIsOffByDefault(): void
    {
        $html = (new MarkdownConverter())->convert('a ==plain== word');

        $this->assertStringNotContainsString('<mark>', $html);
    }

    public function testMarkNestsWithEmphasis(): void
    {
        $converter = new MarkdownConverter(['extensions' => self::EXTENSIONS]);

        $this->assertStringContainsString('<mark>very <strong>bold</strong></mark>', $converter->convert('==very **bold**=='));
    }

    public function testConvertSafeUsesConfiguredExtensions(): void
    {
        $converter = new MarkdownConverter(['extensions' => self::EXTENSIONS, 'html_input' => 'allow']);

        $html = $converter->convertSafe("==hi== <script>alert(1)</script> [x](javascript:alert(1))\n\nnote[^1]\n\n[^1]: text");

        $this->assertStringContainsString('<mark>hi</mark>', $html);
        $this->assertStringContainsString('footnote', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function testConvertSafeWithoutMarkLeavesMarkup(): void
    {
        $html = (new MarkdownConverter(['extensions' => ['commonmark']]))->convertSafe('==hi==');

        $this->assertStringNotContainsString('<mark>', $html);
    }

    public function testLatteFilterRespectsExtensionsAndStaysSafe(): void
    {
        $converter = new MarkdownConverter(['extensions' => self::EXTENSIONS, 'html_input' => 'allow']);
        $filter = new MarkdownFilter($converter);

        $latte = new Engine();
        $latte->setLoader(new StringLoader());
        $latte->addFilter('markdown', [$filter, '__invoke']);
        $latte->addFilter('markdownSafe', [$filter, '__invoke']);

        foreach (['markdown', 'markdownSafe'] as $name) {
            $out = $latte->renderToString('{$text|' . $name . '}', [
                'text' => '==hi== <script>alert(1)</script>',
            ]);

            $this->assertStringContainsString('<mark>hi</mark>', $out, $name);
            $this->assertStringNotContainsString('<script>', $out, $name);
        }
    }
}
