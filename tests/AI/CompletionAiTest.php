<?php

declare(strict_types=1);

namespace Gptsdk\Test\AI;

use Gptsdk\AI\AnthropicAIVendor;
use Gptsdk\AI\CompletionAi;
use Gptsdk\AI\MockVendor;
use Gptsdk\AI\OpenAIVendor;
use Gptsdk\Compilers\DoubleBracketsPromptCompiler;
use Gptsdk\Enum\CompilerType;
use Gptsdk\Storage\GithubPromptStorage;
use Gptsdk\Storage\TempLocalPromptStorage;
use Gptsdk\Types\AiRequest;
use Mockery\Expectation;
use Mockery\MockInterface;
use Ramsey\Dev\Tools\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function base64_encode;
use function json_encode;

class CompletionAiTest extends TestCase
{
    private TempLocalPromptStorage $tempLocalPromptStorage;
    private MockInterface $openAiHttpClient;
    private MockInterface $anthropicHttpClient;
    private MockInterface $githubHttpClient;
    private CompletionAi $completionAi;

    public function setUp(): void
    {
        $this->tempLocalPromptStorage = new TempLocalPromptStorage();
        $this->openAiHttpClient = $this->mockery(HttpClientInterface::class);
        $this->anthropicHttpClient = $this->mockery(HttpClientInterface::class);
        $this->githubHttpClient = $this->mockery(HttpClientInterface::class);

        $this->completionAi = new CompletionAi(
            [
                'openai' => new OpenAIVendor($this->openAiHttpClient),
                'anthropic' => new AnthropicAIVendor($this->anthropicHttpClient),
            ],
            [
                CompilerType::DOUBLE_BRACKETS->value => new DoubleBracketsPromptCompiler(),
            ],
            new GithubPromptStorage(
                $this->githubHttpClient,
                'saassdk',
                'gptsdk-prompts',
                'super-secret-token',
                $this->tempLocalPromptStorage,
            ),
        );
    }

    public function testComplete(): void
    {
        $this->tempLocalPromptStorage->resetPromptCache();
        /** @var Expectation $githubExpectation */
        $githubExpectation = $this->githubHttpClient->expects('request');
        $githubExpectation
            ->once()
            ->andReturn($this->mockery(ResponseInterface::class, [
                'toArray' => [
                    'content' => base64_encode((string) json_encode([
                        'messages' => [['role' => 'User', 'content' => 'Hello [[ai]]!']],
                        'variables' => [['type' => 'string', 'name' => 'ai']],
                        'compilerType' => CompilerType::DOUBLE_BRACKETS,
                    ])),
                ],
                'getStatusCode' => 200,
            ]));

        /** @var Expectation $openaiExpectation */
        $openaiExpectation = $this->openAiHttpClient->expects('request');
        $openaiExpectation
            ->once()
            ->andReturn($this->mockery(ResponseInterface::class, [
                'toArray' => [
                    'choices' => [
                        ['message' => ['content' => 'OpenAI response']],
                    ],
                ],
                'getStatusCode' => 200,
            ]));

        /** @var Expectation $anthropicExpectation */
        $anthropicExpectation = $this->anthropicHttpClient->expects('request');
        $anthropicExpectation
            ->once()
            ->andReturn($this->mockery(ResponseInterface::class, [
                'toArray' => [
                    'completion' => 'Anthropic response',
                ],
                'getStatusCode' => 200,
            ]));

        $responses = $this->completionAi->complete(
            [
                new AiRequest(
                    apiKey: 'secret-openai-key',
                    aiVendor: 'openai',
                    llmOptions: ['model' => 'gpt4o'],
                    promptPath: 'hello.prompt',
                    variableValues: ['ai' => 'OpenAi'],
                ),
                new AiRequest(
                    apiKey: 'secret-anthropic-key',
                    aiVendor: 'anthropic',
                    llmOptions: ['model' => 'gpt4o'],
                    promptPath: 'hello.prompt',
                    variableValues: ['ai' => 'Anthropic'],
                ),
            ],
        );

        $this->assertIsArray($responses[0]->plainResponse);
        $this->assertArrayHasKey('choices', $responses[0]->plainResponse);
        $this->assertIsArray($responses[0]->plainResponse['choices']);
        $this->assertArrayHasKey(0, $responses[0]->plainResponse['choices']);
        $this->assertIsArray($responses[0]->plainResponse['choices'][0]);
        $this->assertArrayHasKey('message', $responses[0]->plainResponse['choices'][0]);
        $this->assertIsArray($responses[0]->plainResponse['choices'][0]['message']);
        $this->assertArrayHasKey('content', $responses[0]->plainResponse['choices'][0]['message']);
        $this->assertSame(
            'OpenAI response',
            $responses[0]->plainResponse['choices'][0]['message']['content'],
        );

        $this->assertNotNull($responses[1]->plainResponse);
        $this->assertArrayHasKey('completion', $responses[1]->plainResponse);
        $this->assertSame(
            'Anthropic response',
            $responses[1]->plainResponse['completion'],
        );
    }

    public function testCompleteMock(): void
    {
        $mockedCompletionAi = new CompletionAi(
            [
                'openai' => new MockVendor(),
            ],
            [
                CompilerType::DOUBLE_BRACKETS->value => new DoubleBracketsPromptCompiler(),
            ],
            new GithubPromptStorage(
                $this->githubHttpClient,
                'saassdk',
                'gptsdk-prompts',
                'super-secret-token',
                $this->tempLocalPromptStorage,
            ),
        );

        $variableValues = ['ai' => 'Man'];

        $this->tempLocalPromptStorage->resetPromptCache();
        /** @var Expectation $githubExpectation */
        $githubExpectation = $this->githubHttpClient->expects('request');
        $githubExpectation
            ->once()
            ->andReturn($this->mockery(ResponseInterface::class, [
                'toArray' => [
                    'content' => base64_encode((string) json_encode([
                        'messages' => [['role' => 'User', 'content' => 'Hello [[ai]]!']],
                        'variables' => [['type' => 'string', 'name' => 'ai']],
                        'compilerType' => CompilerType::DOUBLE_BRACKETS,
                        'mocks' => [
                            sha1((string) json_encode($variableValues)) => [
                                'variableValues' => $variableValues,
                                'output' => ['messages' => ['Hello']]
                            ]
                        ]
                    ])),
                ],
                'getStatusCode' => 200,
            ]));

        $responses = $mockedCompletionAi->complete(
            [
                new AiRequest(
                    apiKey: 'secret-openai-key',
                    aiVendor: 'openai',
                    llmOptions: ['model' => 'gpt4o'],
                    promptPath: 'hello.prompt',
                    variableValues: $variableValues
                )
            ],
        );

        $this->assertIsArray($responses[0]->plainResponse);
        $this->assertArrayHasKey('messages', $responses[0]->plainResponse);
        $this->assertIsArray($responses[0]->plainResponse['messages']);
        $this->assertArrayHasKey(0, $responses[0]->plainResponse['messages']);
        $this->assertSame(
            'Hello',
            $responses[0]->plainResponse['messages'][0]
        );

    }
}
