<?php

/**
 * This file is part of saassdk/gptsdk-php
 *
 * @copyright Copyright (c) andriimoroz <moro97andrze@gmail.com>
 * @license https://opensource.org/license/mit/ MIT License
 */

declare(strict_types=1);

namespace Gptsdk\Interfaces;

use Gptsdk\Types\AiRequest;

interface AILogger
{
    public function log(AiRequest $aiRequest): AiRequest;
}
