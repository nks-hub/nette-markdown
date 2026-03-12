<?php

declare(strict_types=1);

namespace NksHub\Markdown\Extension\Mark;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class MarkRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        Mark::assertInstanceOf($node);

        return new HtmlElement('mark', [], $childRenderer->renderNodes($node->children()));
    }
}
