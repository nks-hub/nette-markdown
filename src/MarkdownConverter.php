<?php

declare(strict_types=1);

namespace NksHub\Markdown;

use InvalidArgumentException;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter as LeagueMarkdownConverter;
use NksHub\Markdown\Extension\Mark\MarkExtension;

class MarkdownConverter
{
    private const KNOWN_EXTENSIONS = [
        'commonmark',
        'gfm',
        'footnote',
        'smartpunct',
        'heading_permalink',
        'toc',
        'mark',
        'external_link',
    ];

    private LeagueMarkdownConverter $converter;
    private ?LeagueMarkdownConverter $safeConverter = null;

    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config = [])
    {
        $this->converter = $this->buildConverter($config);
    }

    public function convert(string $markdown): string
    {
        return (string) $this->converter->convert($markdown);
    }

    /**
     * Convert untrusted input: uses the configured extensions but always strips raw HTML
     * and blocks unsafe links, whatever 'html_input' and 'allow_unsafe_links' say.
     */
    public function convertSafe(string $markdown): string
    {
        $this->safeConverter ??= $this->buildConverter([
            ...$this->config,
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return (string) $this->safeConverter->convert($markdown);
    }

    /** @param array<string, mixed> $config */
    private function buildConverter(array $config): LeagueMarkdownConverter
    {
        $extensions = $config['extensions'] ?? ['commonmark', 'gfm'];
        unset($config['extensions']);

        $envConfig = array_merge([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ], $config);

        $env = new Environment($envConfig);

        foreach ($extensions as $name) {
            foreach ($this->resolveExtension($name) as $extension) {
                $env->addExtension($extension);
            }
        }

        return new LeagueMarkdownConverter($env);
    }

    /**
     * Resolve an extension key to one or more extension instances.
     *
     * The 'gfm' key is a bundle of the four GitHub Flavored Markdown extensions
     * available in league/commonmark 2.x (there is no single GFM class).
     *
     * @return list<\League\CommonMark\Extension\ExtensionInterface>
     *
     * @throws InvalidArgumentException When an unknown extension name is provided.
     */
    private function resolveExtension(string $name): array
    {
        return match ($name) {
            'commonmark' => [new CommonMarkCoreExtension()],
            'gfm' => [
                new TableExtension(),
                new StrikethroughExtension(),
                new TaskListExtension(),
                new AutolinkExtension(),
            ],
            'footnote' => [new FootnoteExtension()],
            'smartpunct' => [new SmartPunctExtension()],
            'heading_permalink' => [new HeadingPermalinkExtension()],
            'toc' => [new TableOfContentsExtension()],
            'mark' => [new MarkExtension()],
            'external_link' => [new ExternalLinkExtension()],
            default => throw new InvalidArgumentException(sprintf(
                'Unknown markdown extension "%s". Known extensions: %s',
                $name,
                implode(', ', self::KNOWN_EXTENSIONS),
            )),
        };
    }
}
