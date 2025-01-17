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

class PromptMessage implements JsonSerializable
{
    public function __construct(
        public readonly string $role,
        public readonly string $content,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }
}
