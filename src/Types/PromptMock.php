<?php

declare(strict_types=1);

namespace Gptsdk\Types;

use JsonSerializable;

class PromptMock implements JsonSerializable
{
    /**
     * @param array<mixed, mixed> $variableValues
     * @param array<mixed, mixed> $output
     */
    public function __construct(
        public readonly array $variableValues,
        public readonly array $output,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'output' => $this->output,
            'variableValues' => $this->variableValues,
        ];
    }
}
