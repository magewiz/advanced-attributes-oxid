<?php

declare(strict_types=1);

namespace Antigravity\AdvancedAttributes\GraphQL\Type;

use Antigravity\AdvancedAttributes\Model\AttributeValue; // Assuming this model exists or we use Base
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @Type(name="AdvancedAttributeValue", class=AttributeValue::class)
 */
class AdvancedAttributeValueType
{
    /**
     * @Field()
     */
    public function getId(AttributeValue $value): string
    {
        return (string) $value->getId();
    }

    /**
     * @Field()
     */
    public function getAttributeId(AttributeValue $value): string
    {
        return (string) $value->getFieldData('oxattrid');
    }

    /**
     * @Field()
     */
    public function getValue(AttributeValue $value): string
    {
        return (string) $value->getFieldData('oxvalue');
    }
}
