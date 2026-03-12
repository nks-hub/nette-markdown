<?php

declare(strict_types=1);

namespace NksHub\Markdown\Extension\Mark;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class MarkExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addDelimiterProcessor(new MarkDelimiterProcessor());
        $environment->addRenderer(Mark::class, new MarkRenderer());
    }
}
