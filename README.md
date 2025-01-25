<h1 align="center">saassdk/gptsdk-php</h1>
<p align="center">
    <a href="https://github.com/saassdk/gptsdk-php"><img src="https://img.shields.io/badge/source-saassdk/gptsdk--php-blue.svg?style=flat-square" alt="Source Code"></a>
    <a href="https://packagist.org/packages/saassdk/gptsdk-php"><img src="https://img.shields.io/packagist/v/saassdk/gptsdk-php.svg?style=flat-square&label=release" alt="Download Package"></a>
    <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/saassdk/gptsdk-php.svg?style=flat-square&colorB=%238892BF" alt="PHP Programming Language"></a>
    <a href="https://github.com/saassdk/gptsdk-php/blob/main/LICENSE"><img src="https://img.shields.io/packagist/l/saassdk/gptsdk-php.svg?style=flat-square&colorB=darkcyan" alt="Read License"></a>
</p>


## About
GptSdk is a powerful library designed to simplify the way you execute and manage prompts dynamically.
By integrating seamlessly with your version control system (VCS),
GptSdk offers a streamlined workflow for creating, organizing, and running prompts.
Whether you’re building AI-powered applications or optimizing your development process,
GptSdk provides the tools you need to succeed.


#### How It Works:

- **Store Prompts in Your Repository**
Prompts are managed as .prompt files within your VCS, ensuring they are versioned, trackable, and collaborative.

- **Dynamic Execution**
The library retrieves and compiles .prompt files dynamically, allowing for real-time updates and zero-latency execution in your applications.

- **Flexible Integration**
Easily integrate GptSdk with your AI workflows. The library supports multiple AI vendors, and its modular design lets you extend functionality as needed.


### Why Use GptSdk?:
- **Centralized Prompt Management** Keep your prompts organized and versioned alongside your code.
- **Dynamic Workflow** Update and execute prompts instantly without delays.
- **Open-Source and Free** Use the library at no cost with the freedom to customize and extend it.

Get started with GptSdk and transform how you manage and execute prompts—dynamic, organized, and scalable.

This project adheres to a [code of conduct](CODE_OF_CONDUCT.md).
By participating in this project and its community, you are expected to
uphold this code.


## Installation

Install this package as a dependency using [Composer](https://getcomposer.org).

``` bash
composer require saassdk/gptsdk-php
```

## Usage
Add GptSdk to your project and initialize it:
``` php

<?php

use Gptsdk\AI\AnthropicAIVendor;
use Gptsdk\AI\CompletionAi;
use Gptsdk\AI\OpenAIVendor;
use Gptsdk\Compilers\DoubleBracketsPromptCompiler;
use Gptsdk\Enum\CompilerType;
use Gptsdk\Storage\GithubPromptStorage;
use Gptsdk\Types\AiRequest;
use Symfony\Component\HttpClient\HttpClient;
use Gptsdk\Storage\TempLocalPromptStorage;

require_once __DIR__ . '/../vendor/autoload.php';


$githubOwner = 'AndriiMz';
$githubRepositoryName = 'gptsdk-prompts';
$githubToken = 'github-token-here';
$openAiToken = 'openai-token-here';

$tempLocalPromptStorage = new TempLocalPromptStorage();
$tempLocalPromptStorage->resetPromptCache();

$completionAi = new CompletionAi(
    [
        'openai' => new OpenAIVendor(HttpClient::create()),
        'anthropic' => new AnthropicAIVendor(HttpClient::create())
    ],
    [
        CompilerType::DOUBLE_BRACKETS->value => new DoubleBracketsPromptCompiler()
    ],
    new GithubPromptStorage(
        HttpClient::create(),
        $githubOwner,
        $githubRepositoryName,
        $githubToken,
        $tempLocalPromptStorage
    )
);

print_r(
    $completionAi->complete([
        new AiRequest(
            apiKey: $openAiToken,
            aiVendor: 'openai',
            llmOptions: ['model' => 'gpt-3.5-turbo'],
            promptPath: 'first1.prompt',
            variableValues: [
                'variable1' => 'Hello'
            ]
        )
    ])[0]->plainResponse
);

```
Store your .prompt files in your repository and reference them by filename when executing.

### Implementing Custom Components
GptSdk is designed to be flexible. You can customize or extend its core components:

- **AILogger**:
  Customize logging behavior to monitor prompt executions.
   ```php
   class CustomLogger implements AILogger {
       public function (AiRequest $aiRequest): AiRequest {
           // Custom log handling logic
           echo $message;
       }
   }
   ```

- **PromptCompiler**:
  Use a custom prompt compilation strategy.
   ```php
   class CustomCompiler implements PromptCompiler {
        public function compile(AiRequest $aiRequest): array {
           // Custom compilation logic
           return str_replace(array_keys($variables), array_values($variables), $template);
       }
   }
   ```

- **AIVendor**:
  Integrate additional or custom AI vendors.
   ```php
   class CustomVendor implements AIVendor {
       public function complete(AiRequest $aiRequest): ResponseInterface {
           // Custom API integration logic

       }
   }
   ```
---

With GptSdk, you have full control over how your prompts are executed. Install the library today and start building smarter workflows.


## Advanced Features for Managing Your Prompts
The GptSdk UI is a powerful companion to the library, providing an intuitive interface for managing, testing, and optimizing your prompts.
This section explains how to connect your repository to the UI, the features it unlocks, and its pricing structure.

---

**1. Connecting Your Repository**
- **Step 1**: Sign in to the GptSdk UI. If you don’t have an account, [create one here](https://app.gpt-sdk.com/signup).
- **Step 2**: Link your version control repository (GitHub, GitLab, or Bitbucket).
- **Step 3**: Sync your prompts stored in the repository to start managing them in the UI.

*Note: All prompts remain securely stored in your repository, ensuring full control and privacy.*

---

**2. Key Features of the GptSdk UI**
- **Prompt Testing**:
  Test your prompts directly in the UI with real or sample data.
    - Run prompts against multiple datasets to compare results.
    - Analyze performance and adjust variables to refine outputs.

- **Directory Organization**:
  Create, edit, and organize prompts into directories for better project management.

- **Version Control Integration**:
  Access the full history of your prompts with version control support.
    - Compare changes between prompt versions.
    - Roll back to previous versions as needed.

- **Team Collaboration**:
  Share prompts with team members and collaborate seamlessly. Use branching and pull requests to review and refine.

- **Execution History**:
  Navigate through a detailed history of prompt executions. Identify the best results and replicate them easily.

---

**3. Pricing**
- **Free Features**:
    - Store and execute prompts directly in your repository.
    - Use GptSdk library for prompt management without any cost.

- **Paid UI Features**:
    - Access the GptSdk UI for $25/month per repository.
    - Enjoy enhanced tools for testing, collaboration, and organization.

---

The GptSdk UI takes prompt management to the next level. Connect your repository today to unlock its full potential and streamline your workflows.

## Resources
Dive deeper into prompt management, explore best practices, and stay updated with the latest developments. Here’s a collection of articles and guides to help you maximize the potential of GptSdk.

---
**Resources List:**

---

Check back often as we continue to add more articles, guides, and tutorials to help you get the most out of GptSdk.

## Join the GptSdk Community
The GptSdk community is where developers, AI enthusiasts, and teams come together to share knowledge, solve problems, and inspire innovation.
Connect with us on your favorite platforms and become part of the conversation.

---

**Where to Connect**:
1. **[LinkedIn](https://www.linkedin.com/company/gptsdk/)**
   *Follow us on LinkedIn for the latest updates, industry insights, and professional networking opportunities.*

2. **[Twitter](https://x.com/gptsdk)**
   *Stay in the loop with real-time announcements, tips, and discussions. Join the conversation using #GPTSDK.*

3. **[Slack](https://join.slack.com/t/saassdk/shared_invite/zt-2yg24cv91-IDyjYgoVgEeqUzuhF6qDYA)**
   *Collaborate with fellow developers, ask questions, and get support in our active Slack community.*

---

**What You’ll Find in the Community**:
- **Discussions and Q&A**:
  Get advice, share your expertise, and explore ideas with other members.

- **Support and Troubleshooting**:
  Find help with implementation, debugging, and optimization from both GPTSDK experts and fellow users.

- **Exclusive Updates**:
  Be the first to know about new features, releases, and opportunities to contribute.

---

Your input drives the future of GptSdk. Join the community today and help shape the next generation of prompt management tools!

## Contributing

Contributions are welcome! To contribute, please familiarize yourself with
[CONTRIBUTING.md](CONTRIBUTING.md).

## Coordinated Disclosure

Keeping user information safe and secure is a top priority, and we welcome the
contribution of external security researchers. If you believe you've found a
security issue in software that is maintained in this repository, please read
[SECURITY.md](SECURITY.md) for instructions on submitting a vulnerability report.


## Copyright and License

saassdk/gptsdk-php is copyright © [andriimoroz](https://gpt-sdk.com)
and licensed for use under the terms of the
MIT License (MIT). Please see [LICENSE](LICENSE) for more information.


