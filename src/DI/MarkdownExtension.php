<?php

declare(strict_types=1);

namespace NksHub\Markdown\DI;

use Latte\Engine;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use NksHub\Markdown\Latte\MarkdownFilter;
use NksHub\Markdown\MarkdownConverter;

class MarkdownExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'extensions' => Expect::arrayOf('string')->default(['commonmark', 'gfm']),
            'html_input' => Expect::anyOf('strip', 'allow', 'escape')->default('strip'),
            'allow_unsafe_links' => Expect::bool()->default(false),
            'heading_permalink' => Expect::structure([
                'symbol' => Expect::string()->default('#'),
            ])->castTo('array'),
            'external_link' => Expect::structure([
                'internal_hosts' => Expect::anyOf(
                    Expect::string(),
                    Expect::arrayOf('string'),
                )->default([]),
                'open_in_new_window' => Expect::bool()->default(false),
                'html_class' => Expect::string()->default(''),
                'nofollow' => Expect::anyOf('', 'all', 'external', 'internal')->default('external'),
                'noopener' => Expect::anyOf('', 'all', 'external', 'internal')->default('external'),
                'noreferrer' => Expect::anyOf('', 'all', 'external', 'internal')->default('external'),
            ])->castTo('array'),
        ])->castTo('array');
    }

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $config = (array) $this->getConfig();

        $converterConfig = [
            'extensions' => $config['extensions'],
            'html_input' => $config['html_input'],
            'allow_unsafe_links' => $config['allow_unsafe_links'],
        ];

        if (!empty($config['heading_permalink']) && in_array('heading_permalink', $config['extensions'], true)) {
            $converterConfig['heading_permalink'] = $config['heading_permalink'];
        }

        if (in_array('external_link', $config['extensions'], true)) {
            $converterConfig['external_link'] = $config['external_link'];
        }

        $builder->addDefinition($this->prefix('converter'))
            ->setFactory(MarkdownConverter::class, [$converterConfig])
            ->setType(MarkdownConverter::class);

        $builder->addDefinition($this->prefix('filter'))
            ->setFactory(MarkdownFilter::class)
            ->setType(MarkdownFilter::class);
    }

    public function beforeCompile(): void
    {
        $builder = $this->getContainerBuilder();

        foreach ($builder->findByType(Engine::class) as $def) {
            $def->addSetup('addFilter', ['markdown', [$this->prefix('@filter'), '__invoke']]);
            $def->addSetup('addFilter', ['markdownSafe', [$this->prefix('@filter'), '__invoke']]);
            $def->addSetup('addFilter', ['markdownRaw', [$this->prefix('@filter'), 'raw']]);
        }
    }
}
