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

    public function __invoke(string $content): Html
    {
        return new Html($this->converter->convert($content));
    }
}
