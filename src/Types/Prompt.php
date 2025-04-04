<?php

/**
 * This file is part of saassdk/gptsdk-php
 *
 * @copyright Copyright (c) andriimoroz <moro97andrze@gmail.com>
 * @license https://opensource.org/license/mit/ MIT License
 */

declare(strict_types=1);

namespace Gptsdk\Types;

use Gptsdk\Enum\CompilerType;

class Prompt
{
    /**
     * @param PromptMessage[] $messages
     * @param PromptVariable[] $variables
     * @param PromptMock[] $mocks
     */
    public function __construct(
        public readonly string $path,
        public readonly array $messages,
        public readonly array $variables,
        public readonly CompilerType $compilerType,
        public readonly array $mocks,
    ) {
    }
}
