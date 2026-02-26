<?php

declare(strict_types=1);

namespace Antigravity\AdvancedAttributes\GraphQL\Type;

use Antigravity\AdvancedAttributes\Model\Attribute;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @Type(name="AdvancedAttribute", class=Attribute::class)
 */
class AdvancedAttributeType
{
    /**
     * @Field()
     */
    public function getId(Attribute $attribute): string
    {
        return (string) $attribute->getId();
    }

    /**
     * @Field()
     */
    public function getTitle(Attribute $attribute): string
    {
        return (string) $attribute->getTitle();
    }

    /**
     * @Field()
     */
    public function getType(Attribute $attribute): string
    {
        return (string) $attribute->getFieldData('oxtype');
    }
}
