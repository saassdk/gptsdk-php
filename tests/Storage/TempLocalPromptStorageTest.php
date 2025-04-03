<?php

declare(strict_types=1);

namespace Gptsdk\Test\Storage;

use Gptsdk\Enum\CompilerType;
use Gptsdk\Storage\TempLocalPromptStorage;
use Gptsdk\Test\TestCase;
use Gptsdk\Types\Prompt;
use Gptsdk\Types\PromptMessage;
use Gptsdk\Types\PromptVariable;

class TempLocalPromptStorageTest extends TestCase
{
    private TempLocalPromptStorage $storage;

    protected function setUp(): void
    {
        $this->storage = new TempLocalPromptStorage();
    }

    public function testGetPrompt(): void
    {
        $this->storage->resetPromptCache();
        $promptPath = 'general-prompts/myprompt.prompt';
        $this->assertNull(
            $this->storage->getPrompt($promptPath),
        );

        $this->storage->setPromptCache(
            new Prompt(
                $promptPath,
                [new PromptMessage('User', 'Hello OpenAI!')],
                [new PromptVariable('who', 'string')],
                CompilerType::DOUBLE_BRACKETS,
                mocks: [],
            ),
        );

        $prompt = $this->storage->getPrompt($promptPath);
        $this->assertNotNull($prompt);
        $this->assertSame($promptPath, $prompt->path);
        $this->assertSame('User', $prompt->messages[0]->role);
        $this->assertSame('who', $prompt->variables[0]->name);
        $this->assertSame('double_brackets', $prompt->compilerType->value);
    }
}
