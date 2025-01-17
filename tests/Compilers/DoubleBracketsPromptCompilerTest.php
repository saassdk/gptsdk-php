<?php

declare(strict_types=1);

namespace Gptsdk\Test\Compilers;

use Gptsdk\Compilers\DoubleBracketsPromptCompiler;
use Gptsdk\Types\AiRequest;
use Gptsdk\Types\PromptMessage;
use Ramsey\Dev\Tools\TestCase;

class DoubleBracketsPromptCompilerTest extends TestCase
{
    private DoubleBracketsPromptCompiler $compiler;

    protected function setUp(): void
    {
        $this->compiler = new DoubleBracketsPromptCompiler();
    }

    public function testCompile(): void
    {
        $compiledPrompt = $this->compiler->compile(
            new AiRequest(
                'secret-key',
                'openai',
                [],
                [
                    new PromptMessage('User', 'Hello [[ai]]!'),
                ],
                variableValues: [
                    'ai' => 'OpenAi',
                ],
            ),
        );

        $this->assertSame(
            'Hello OpenAi!',
            $compiledPrompt[0]->content,
        );
    }
}
