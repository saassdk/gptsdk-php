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
use Gptsdk\Enum\Status;

class AiRequest
{
    /**
     * @param array<string, string> $llmOptions
     * @param array<string, string> $variableValues
     * @param PromptMessage[]|null $messages
     * @param PromptMessage[]|null $compiledMessages
     * @param array<array-key, mixed>|null $plainResponse
     * @param array<string, string> $payload
     */
    public function __construct(
        public readonly string $apiKey,
        public readonly string $aiVendor,
        public readonly array $llmOptions,
        public readonly ?array $messages = null,
        public readonly ?CompilerType $compilerType = null,
        public readonly ?Prompt $prompt = null,
        public readonly ?string $promptPath = null,
        public readonly array $variableValues = [],
        public readonly ?array $compiledMessages = null,
        public readonly ?array $plainResponse = null,
        public readonly ?Status $responseStatus = null,
        public array $payload = [],
    ) {
    }
}
