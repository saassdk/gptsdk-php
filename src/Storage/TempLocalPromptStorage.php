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
use Gptsdk\Interfaces\PromptStorage;
use Gptsdk\Types\Prompt;
use Gptsdk\Types\PromptMessage;
use Gptsdk\Types\PromptMock;
use Gptsdk\Types\PromptVariable;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function array_map;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function is_array;
use function is_dir;
use function json_decode;
use function json_encode;
use function mkdir;
use function rmdir;
use function str_replace;
use function sys_get_temp_dir;
use function unlink;

use const DIRECTORY_SEPARATOR;

class TempLocalPromptStorage implements PromptStorage
{
    private const PROMPTS_DIR = 'prompts';

    public function getPrompt(string $promptPath): ?Prompt
    {
        $filePath = implode(
            DIRECTORY_SEPARATOR,
            [
                sys_get_temp_dir(),
                self::PROMPTS_DIR,
                $this->convertPromptPath($promptPath),
            ],
        );

        if (!file_exists($filePath)) {
            return null;
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            return null;
        }

        $promptArray = (array) json_decode($fileContent, true);

        return new Prompt(
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
            compilerType: CompilerType::tryFrom((string) ($promptArray['compilerType'] ?? '')) ??
                CompilerType::DOUBLE_BRACKETS,
            mocks: array_map(
                fn (array $mock) => new PromptMock(
                    variableValues: (array) $mock['variableValues'],
                    output: (array) $mock['output'],
                ),
                (array) ($promptArray['mocks'] ?? []),
            ),
        );
    }

    public function resetPromptCache(): void
    {
        $promptsDir = implode(DIRECTORY_SEPARATOR, [sys_get_temp_dir(), self::PROMPTS_DIR]);

        if (!is_dir($promptsDir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($promptsDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        /** @var SplFileInfo $item */
        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }
    }

    public function setPromptCache(Prompt $prompt): void
    {
        $promptsDir = implode(DIRECTORY_SEPARATOR, [sys_get_temp_dir(), self::PROMPTS_DIR]);

        if (!is_dir($promptsDir)) {
            mkdir($promptsDir, 0777, true);
        }

        $filePath = implode(
            DIRECTORY_SEPARATOR,
            [
                $promptsDir,
                $this->convertPromptPath($prompt->path),
            ],
        );

        file_put_contents($filePath, json_encode([
            'messages' => $prompt->messages,
            'variables' => $prompt->variables,
            'compilerType' => $prompt->compilerType->value,
        ]));
    }

    private function convertPromptPath(string $promptPath): string
    {
        return str_replace(
            DIRECTORY_SEPARATOR,
            '__',
            $promptPath,
        );
    }
}
