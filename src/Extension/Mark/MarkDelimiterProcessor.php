<?php

declare(strict_types=1);

namespace NksHub\Markdown\Extension\Mark;

use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Node\Inline\AbstractStringContainer;

final class MarkDelimiterProcessor implements DelimiterProcessorInterface
{
    public function getOpeningCharacter(): string
    {
        return '=';
    }

    public function getClosingCharacter(): string
    {
        return '=';
    }

    public function getMinLength(): int
    {
        return 2;
    }

    public function getDelimiterUse(DelimiterInterface $opener, DelimiterInterface $closer): int
    {
        if ($opener->getLength() >= 2 && $closer->getLength() >= 2) {
            return 2;
        }

        return 0;
    }

    public function process(AbstractStringContainer $opener, AbstractStringContainer $closer, int $delimiterUse): void
    {
        $mark = new Mark();

        $tmp = $opener->next();
        while ($tmp !== null && $tmp !== $closer) {
            $next = $tmp->next();
            $mark->appendChild($tmp);
            $tmp = $next;
        }

        $opener->insertAfter($mark);
    }
}
