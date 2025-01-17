<?php

/**
 * This file is part of saassdk/gptsdk-php
 *
 * @copyright Copyright (c) andriimoroz <moro97andrze@gmail.com>
 * @license https://opensource.org/license/mit/ MIT License
 */

declare(strict_types=1);

namespace Gptsdk\Storage;

use Gptsdk\Enum\CompilerType;
use Gptsdk\Execption\PromptStorageIssue;
use Gptsdk\Interfaces\PromptStorage;
use Gptsdk\Types\Prompt;
use Gptsdk\Types\PromptMessage;
use Gptsdk\Types\PromptVariable;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function array_map;
use function base64_decode;
use function is_array;
use function json_decode;

class GithubPromptStorage implements PromptStorage
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $owner,
        private readonly string $repositoryName,
        private readonly string $token,
        private readonly ?PromptStorage $cacheStorage = null,
    ) {
    }

    public function getPrompt(string $promptPath): ?Prompt
    {
        $prompt = $this->cacheStorage?->getPrompt($promptPath);

        if ($prompt === null) {
            $githubResponse = $this->httpClient->request(
                'GET',
                "https://api.github.com/repos/$this->owner/$this->repositoryName/contents/$promptPath",
                [
                    'headers' => [
                        'X-GitHub-Api-Version' => '2022-11-28',
                        'Accept' => 'application/vnd.github+json',
                        'Authorization' => "Bearer $this->token",
                    ],
                ],
            );

            $file = $githubResponse->toArray(false);

            if ($githubResponse->getStatusCode() !== 200) {
                throw new PromptStorageIssue(
                    "Failed to get prompt from github: {$file['message']}",
                );
            }

            if (!isset($file['content'])) {
                return null;
            }

            $promptArray = (array) json_decode(base64_decode(
                (string) $file['content'],
            ), true);

            $prompt = new Prompt(
                path: $promptPath,
                messages: array_map(
                    fn (array $message) => new PromptMessage(
                        role: (string) ($message['role'] ?? ''),
                        content: (string) ($message['content'] ?? ''),
                    ),
                    is_array($promptArray['messages']) ? $promptArray['messages'] : [],
                ),
                variables: array_map(
                    fn (array $variable) => new PromptVariable(
                        name: (string) ($variable['name'] ?? ''),
                        type: (string) ($variable['type'] ?? ''),
                        note: isset($variable['note']) ? (string) $variable['note'] : null,
                    ),
                    is_array($promptArray['variables']) ? $promptArray['variables'] : [],
                ),
                compilerType:
                    CompilerType::tryFrom((string) ($promptArray['compilerType'] ?? '')) ??
                    CompilerType::DOUBLE_BRACKETS,
            );

            $this->cacheStorage?->setPromptCache($prompt);

            return $prompt;
        }

        return $prompt;
    }

    public function resetPromptCache(): void
    {
        $this->cacheStorage?->resetPromptCache();
    }

    public function setPromptCache(Prompt $prompt): void
    {
        $this->cacheStorage?->setPromptCache($prompt);
    }
}
