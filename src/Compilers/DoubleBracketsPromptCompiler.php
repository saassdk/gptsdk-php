<?php

/**
 * This file is part of saassdk/gptsdk-php
 *
 * @copyright Copyright (c) andriimoroz <moro97andrze@gmail.com>
 * @license https://opensource.org/license/mit/ MIT License
 */

declare(strict_types=1);

namespace Gptsdk\Compilers;

use Gptsdk\Interfaces\PromptCompiler;
use Gptsdk\Types\AiRequest;
use Gptsdk\Types\PromptMessage;

use function array_keys;
use function array_map;
use function array_values;
use function str_replace;

class DoubleBracketsPromptCompiler implements PromptCompiler
{
    /**
     * @return PromptMessage[]
     */
    public function compile(AiRequest $aiRequest): array
    {
        $replaceKeys = array_map(
            fn ($key) => "[[$key]]",
            array_keys($aiRequest->variableValues),
        );
        $replaceVariables = array_values($aiRequest->variableValues);

        return array_map(
            fn (PromptMessage $prompt) => new PromptMessage(
                role: $prompt->role,
                content: str_replace(
                    $replaceKeys,
                    $replaceVariables,
                    $prompt->content,
                ),
            ),
            $aiRequest->messages ?? [],
        );
    }
}
