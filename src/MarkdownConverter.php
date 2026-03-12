<?php

declare(strict_types=1);

namespace NksHub\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter as LeagueMarkdownConverter;

class MarkdownConverter
{
    private LeagueMarkdownConverter $converter;

    /** @param array<string, mixed> $config */
    public function __construct(array $config = [])
    {
        $this->converter = $this->buildConverter($config);
    }

    public function convert(string $markdown): string
    {
        return (string) $this->converter->convert($markdown);
    }

    public function convertSafe(string $markdown): string
    {
        $safeConfig = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ];

        $env = new Environment($safeConfig);
        $env->addExtension(new CommonMarkCoreExtension());
        $env->addExtension(new TableExtension());
        $env->addExtension(new StrikethroughExtension());
        $env->addExtension(new TaskListExtension());
        $env->addExtension(new AutolinkExtension());

        $safeConverter = new LeagueMarkdownConverter($env);

        return (string) $safeConverter->convert($markdown);
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
            default => [],
        };
    }
}
