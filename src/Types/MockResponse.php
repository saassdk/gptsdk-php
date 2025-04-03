<?php

declare(strict_types=1);

namespace Gptsdk\Types;

use Symfony\Contracts\HttpClient\ResponseInterface;

use function json_encode;

class MockResponse implements ResponseInterface
{
    /**
     * @param array<mixed> $output
     */
    public function __construct(private readonly array $output)
    {
    }

    public function getStatusCode(): int
    {
        return 200;
    }

    /**
     * @return array<array<string>>
     */
    public function getHeaders(bool $throw = true): array
    {
        return [];
    }

    public function getContent(bool $throw = true): string
    {
        return (string) json_encode($this->output);
    }

    /**
     * @return array<mixed>
     */
    public function toArray(bool $throw = true): array
    {
        return $this->output;
    }

    public function cancel(): void
    {
    }

    public function getInfo(?string $type = null): mixed
    {
        return [];
    }
}
