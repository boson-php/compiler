<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Task\CreateBoxConfigTask;
use Boson\Component\Compiler\Workflow\Task\CreateEntrypointTask;
use Boson\Component\Compiler\Workflow\Task\DownloadTask;
use Boson\Component\Compiler\Workflow\Task\PackTask;
use Boson\Component\Compiler\Workflow\Task\TaskInterface;

final readonly class PackWorkflow implements TaskInterface
{
    public function __invoke(Configuration $config): int
    {
        Task::info('Pack an application');

        Task::run($config, new PrepareWorkflow());

        Task::run($config, new CreateEntrypointTask());
        Task::run($config, new CreateBoxConfigTask());

        Task::run($config, new DownloadTask(
            sourceUri: $config->boxUri,
            targetPathname: $config->boxPharPathname,
        ));

        Task::run($config, new PackTask(
            boxConfigPathname: $config->boxConfigPathname,
            boxPharPathname: $config->boxPharPathname,
        ));

        return 0;
    }
}
