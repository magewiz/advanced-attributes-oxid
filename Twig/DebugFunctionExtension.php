<?php

namespace Antigravity\AdvancedAttributes\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DebugFunctionExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('bdebug', [$this, 'bFunction']),
        ];
    }

    /**
     * Triggers xdebug_break() if the function exists.
     */
    public function bFunction(): void
    {
        if (function_exists('xdebug_break')) {
            xdebug_break();
        }
    }
}
