<?php

/**
 * This file is part of saassdk/gptsdk-php
 *
 * @copyright Copyright (c) andriimoroz <moro97andrze@gmail.com>
 * @license https://opensource.org/license/mit/ MIT License
 */

declare(strict_types=1);

namespace Gptsdk\AI;

use Gptsdk\Interfaces\AIVendor;
use Gptsdk\Types\AiRequest;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function array_merge;

class AnthropicAIVendor implements AIVendor
{
    private const DEFAULT_VERSION = '2023-06-01';

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function complete(AiRequest $aiRequest): ResponseInterface
    {
        return $this->httpClient->request(
            'POST',
            'https://api.anthropic.com/v1/messages',
            [
                'headers' => [
                    'x-api-key' => $aiRequest->apiKey,
                    'anthropic-version' => $aiRequest->llmOptions['version'] ?? self::DEFAULT_VERSION,
                    'content-type' => 'application/json',
                ],
                'json' => array_merge(
                    $aiRequest->llmOptions,
                    [
                        'messages' => $aiRequest->compiledMessages,
                    ],
                    [
                        'max_tokens' => 1000,
                    ],
                ),
            ],
        );
    }
}
