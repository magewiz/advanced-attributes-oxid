<?php

declare(strict_types=1);

namespace Antigravity\AdvancedAttributes\GraphQL\Input;

use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Factory;

#[Input(name: "AdvancedAttributeInput")]
class AdvancedAttributeInput
{
    public function __construct(
        #[Field]
        private string $title,
        #[Field]
        private string $type
    ) {
    }

    #[Factory]
    public static function fromUserInput(string $title, string $type): self
    {
        return new self($title, $type);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
