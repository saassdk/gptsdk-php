<?php

/**
 * This file is part of saassdk/gptsdk-php
 *
 * @copyright Copyright (c) andriimoroz <moro97andrze@gmail.com>
 * @license https://opensource.org/license/mit/ MIT License
 */

declare(strict_types=1);

namespace Gptsdk\Interfaces;

use Gptsdk\Types\Prompt;

interface PromptStorage
{
    public function getPrompt(string $promptPath): ?Prompt;

    public function setPromptCache(Prompt $prompt): void;

    public function resetPromptCache(): void;
}
