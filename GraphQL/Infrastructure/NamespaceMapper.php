<?php

declare(strict_types=1);

namespace Antigravity\AdvancedAttributes\GraphQL\Infrastructure;

use OxidEsales\GraphQL\Base\Framework\NamespaceMapperInterface;

class NamespaceMapper implements NamespaceMapperInterface
{
    public function getControllerNamespaceMapping(): array
    {
        return [
            'Antigravity\\AdvancedAttributes\\GraphQL\\Controller' => __DIR__ . '/../Controller/',
        ];
    }

    public function getTypeNamespaceMapping(): array
    {
        return [
            'Antigravity\\AdvancedAttributes\\GraphQL\\Type' => __DIR__ . '/../Type/',
            'Antigravity\\AdvancedAttributes\\GraphQL\\Input' => __DIR__ . '/../Input/',
        ];
    }
}
