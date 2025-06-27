<?php

declare(strict_types=1);

namespace Gptsdk\AI;

use Gptsdk\Interfaces\AIVendor;
use Gptsdk\Types\AiRequest;
use Gptsdk\Types\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function array_diff;
use function count;

class MockVendor implements AIVendor
{
    public function complete(AiRequest $aiRequest): ResponseInterface
    {
        $rightMock = null;
        foreach ($aiRequest->prompt->mocks as $mock) {
            if (count(array_diff($mock->variableValues, $aiRequest->variableValues)) === 0) {
                $rightMock = $mock;
            }
        }

        return new MockResponse($rightMock?->output ?? []);
    }
}
