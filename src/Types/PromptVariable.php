<?php

/**
 * This file is part of saassdk/gptsdk-php
 *
 * @copyright Copyright (c) andriimoroz <moro97andrze@gmail.com>
 * @license https://opensource.org/license/mit/ MIT License
 */

declare(strict_types=1);

namespace Gptsdk\Types;

use JsonSerializable;

use function implode;

use const PHP_EOL;

class PromptVariable implements JsonSerializable
{
    /**
     * @param array<string> $options
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly ?string $note = null,
        public readonly ?array $options = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'note' => $this->note,
            'options' => $this->options !== null ? implode(PHP_EOL, $this->options) : null,
        ];
    }
}
