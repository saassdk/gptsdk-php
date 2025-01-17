<?php

/**
 * This file is part of saassdk/gptsdk-php
 *
 * @copyright Copyright (c) andriimoroz <moro97andrze@gmail.com>
 * @license https://opensource.org/license/mit/ MIT License
 */

declare(strict_types=1);

namespace Gptsdk\AI;

use Gptsdk\Enum\Status;
use Gptsdk\Interfaces\AILogger;
use Gptsdk\Interfaces\AIVendor;
use Gptsdk\Interfaces\PromptCompiler;
use Gptsdk\Interfaces\PromptStorage;
use Gptsdk\Types\AiRequest;
use Throwable;

use function array_map;

class CompletionAi
{
    /**
     * @param AIVendor[] $aiVendors
     * @param PromptCompiler[] $promptCompilers
     */
    public function __construct(
        private readonly array $aiVendors,
        private readonly array $promptCompilers,
        private readonly PromptStorage $promptStorage,
        private readonly ?AILogger $aiLogger = null,
    ) {
    }

    /**
     * @param AiRequest[] $aiRequests
     *
     * @return AiRequest[]
     */
    public function complete(array $aiRequests): array
    {
        $aiRequests = $this->fetchPrompt($aiRequests);
        $aiRequests = $this->compile($aiRequests);
        $httpRequests = [];
        foreach ($aiRequests as $index => $aiRequest) {
            if ($aiRequest->responseStatus !== null) {
                $httpRequests[$index] = $aiRequest;

                continue;
            }

            $httpRequests[$index] = $this->aiVendors[$aiRequest->aiVendor]->complete(
                $aiRequest,
            );
        }

        $aiResponses = [];
        foreach ($httpRequests as $index => $httpRequest) {
            $aiRequest = $aiRequests[$index];

            if ($httpRequest instanceof AiRequest) {
                $aiResponses[$index] = $aiRequest;

                continue;
            }

            $aiResponses[$index] = new AiRequest(
                apiKey: $aiRequest->apiKey,
                aiVendor: $aiRequest->aiVendor,
                llmOptions: $aiRequest->llmOptions,
                prompt: $aiRequest->prompt,
                messages: $aiRequest->messages,
                compilerType: $aiRequest->compilerType,
                promptPath: $aiRequest->promptPath,
                variableValues: $aiRequest->variableValues,
                compiledMessages: $aiRequest->compiledMessages,
                plainResponse: $httpRequest->toArray(false),
                responseStatus: $httpRequest->getStatusCode() !== 200 ? Status::ERROR : Status::SUCCESS,
                payload: $aiRequest->payload,
            );
        }

        return array_map(
            fn ($aiRequest) => $this->aiLogger?->log($aiRequest) ?? $aiRequest,
            $aiResponses,
        );
    }

    /**
     * @param AiRequest[] $aiRequests
     *
     * @return AiRequest[]
     */
    private function compile(array $aiRequests): array
    {
        return array_map(
            function (AiRequest $aiRequest) {
                if ($aiRequest->responseStatus !== null) {
                    return $aiRequest;
                }

                $compiledMessages = null;
                $plainResponse = null;
                $responseStatus = null;
                try {
                    $compilerTypeValue = $aiRequest->compilerType?->value;
                    $compiler = $compilerTypeValue !== null ? $this->promptCompilers[$compilerTypeValue] ?? null : null;
                    $compiledMessages = $compiler?->compile($aiRequest);
                } catch (Throwable $e) {
                    $plainResponse = ['error' => $e->getMessage()];
                    $responseStatus = Status::ERROR;
                }

                return new AiRequest(
                    apiKey: $aiRequest->apiKey,
                    aiVendor: $aiRequest->aiVendor,
                    llmOptions: $aiRequest->llmOptions,
                    prompt: $aiRequest->prompt,
                    messages: $aiRequest->messages,
                    compilerType: $aiRequest->compilerType,
                    promptPath: $aiRequest->promptPath,
                    variableValues: $aiRequest->variableValues,
                    payload: $aiRequest->payload,
                    compiledMessages: $compiledMessages,
                    plainResponse: $plainResponse,
                    responseStatus: $responseStatus,
                );
            },
            $aiRequests,
        );
    }

    /**
     * @param AiRequest[] $aiRequests
     *
     * @return AiRequest[]
     */
    private function fetchPrompt(array $aiRequests): array
    {
        return array_map(
            function (AiRequest $aiRequest) {
                if ($aiRequest->messages !== null || $aiRequest->promptPath === null) {
                    return $aiRequest;
                }

                $prompt = null;
                $plainResponse = null;
                $responseStatus = null;
                try {
                    $prompt = $this->promptStorage->getPrompt($aiRequest->promptPath);
                } catch (Throwable $e) {
                    $plainResponse = ['error' => $e->getMessage()];
                    $responseStatus = Status::ERROR;
                }

                return new AiRequest(
                    apiKey: $aiRequest->apiKey,
                    aiVendor: $aiRequest->aiVendor,
                    llmOptions: $aiRequest->llmOptions,
                    prompt: $prompt,
                    messages: $prompt?->messages,
                    compilerType: $prompt?->compilerType,
                    promptPath: $aiRequest->promptPath,
                    variableValues: $aiRequest->variableValues,
                    payload: $aiRequest->payload,
                    plainResponse: $plainResponse,
                    responseStatus: $responseStatus,
                );
            },
            $aiRequests,
        );
    }
}
