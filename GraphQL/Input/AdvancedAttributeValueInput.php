<?php

declare(strict_types=1);

namespace Antigravity\AdvancedAttributes\GraphQL\Input;

use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Factory;

#[Input(name: "AdvancedAttributeValueInput")]
class AdvancedAttributeValueInput
{
    public function __construct(
        #[Field]
        private string $value
    ) {
    }

    #[Factory]
    public static function fromUserInput(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
