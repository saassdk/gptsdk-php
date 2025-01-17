<?php

declare(strict_types=1);

namespace Gptsdk\Test\Storage;

use Gptsdk\Enum\CompilerType;
use Gptsdk\Storage\GithubPromptStorage;
use Gptsdk\Storage\TempLocalPromptStorage;
use Gptsdk\Test\TestCase;
use Mockery\Expectation;
use Mockery\MockInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function base64_encode;
use function json_encode;

class GithubPromptStorageTest extends TestCase
{
    private GithubPromptStorage $promptStorage;
    private TempLocalPromptStorage $localPromptStorage;
    private MockInterface $httpClientMock;

    protected function setUp(): void
    {
        $this->localPromptStorage = new TempLocalPromptStorage();

        $this->httpClientMock = $this->mockery(
            HttpClientInterface::class,
        );

        $this->promptStorage = new GithubPromptStorage(
            $this->httpClientMock,
            'saassdk',
            'gptsdk-prompts',
            'github-secret-token',
            $this->localPromptStorage,
        );
    }

    public function testGetPrompt(): void
    {
        $this->localPromptStorage->resetPromptCache();

        $responseContent = [
            'content' => base64_encode((string) json_encode([
                'messages' => [['role' => 'User', 'content' => 'hello']],
                'variables' => [['name' => 'var1', 'type' => 'string']],
                'compilerType' => CompilerType::DOUBLE_BRACKETS,
            ])),
        ];

        /** @var Expectation $httpClientExpectation */
        $httpClientExpectation = $this->httpClientMock->expects('request');
        $httpClientExpectation
            ->once()
            ->andReturn($this->mockery(ResponseInterface::class, [
                'toArray' => $responseContent,
                'getStatusCode' => 200,
            ]));

        $promptPath = 'super-prompts/myprompt.prompt';

        $prompt = $this->promptStorage->getPrompt($promptPath);
        $this->assertNotNull($prompt);
        $this->assertEquals('hello', $prompt->messages[0]->content);

        //Prompt is cached
        $prompt = $this->promptStorage->getPrompt($promptPath);
        $this->assertNotNull($prompt);
        $this->assertEquals('hello', $prompt->messages[0]->content);
    }
}
