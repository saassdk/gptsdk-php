<?php

declare(strict_types=1);

namespace Gptsdk\AI;

use Gptsdk\Interfaces\AIVendor;
use Gptsdk\Types\AiRequest;
use Gptsdk\Types\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function json_encode;
use function sha1;

class MockVendor implements AIVendor
{
    public function complete(AiRequest $aiRequest): ResponseInterface
    {
        $mockHash = sha1((string) json_encode($aiRequest->variableValues));

        return new MockResponse($aiRequest->prompt->mocks[$mockHash]?->output ?? []);
    }
}
