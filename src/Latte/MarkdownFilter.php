<?php

declare(strict_types=1);

namespace NksHub\Markdown\Latte;

use Latte\Runtime\Html;
use NksHub\Markdown\MarkdownConverter;

class MarkdownFilter
{
    public function __construct(private readonly MarkdownConverter $converter)
    {
    }

    /**
     * Default filter: always uses safe conversion (html_input=strip, no unsafe links).
     */
    public function __invoke(string $content): Html
    {
        return new Html($this->converter->convertSafe($content));
    }

    /**
     * Raw filter: uses the converter as configured (respects user config including html_input=allow).
     */
    public function raw(string $content): Html
    {
        return new Html($this->converter->convert($content));
    }
}
