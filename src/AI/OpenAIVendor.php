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

class OpenAIVendor implements AIVendor
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function complete(AiRequest $aiRequest): ResponseInterface
    {
        return $this->httpClient->request(
            'POST',
            'https://api.openai.com/v1/chat/completions',
            [
                'auth_bearer' => $aiRequest->apiKey,
                'json' => array_merge(
                    $aiRequest->llmOptions,
                    ['messages' => $aiRequest->compiledMessages],
                ),
            ],
        );
    }
}
