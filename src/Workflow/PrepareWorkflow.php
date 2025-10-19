<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Task\CreateDirectoryTask;
use Boson\Component\Compiler\Workflow\Task\CreateFileTask;
use Boson\Component\Compiler\Workflow\Task\TaskInterface;

final readonly class PrepareWorkflow implements TaskInterface
{
    public function __invoke(Configuration $config): void
    {
        Task::info('Prepare build files');

        Task::run($config, new CreateDirectoryTask(
            directory: $config->output,
        ));

        Task::run($config, new CreateFileTask(
            pathname: $config->output . '/.gitignore',
            content: <<<'GITIGNORE'
                *
                !.gitignore
                GITIGNORE
        ));

        Task::run($config, new CreateDirectoryTask(
            directory: $config->temp,
        ));
    }
}
