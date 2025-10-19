<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\CopyFilesTask;
use Boson\Component\Compiler\Workflow\Task\CopyFileTask;

final readonly class PharTarget extends Target
{
    public function compile(Configuration $config): void
    {
        $directory = $this->getBuildDirectory($config);

        Task::run($config, new CopyFileTask(
            sourcePathname: $config->pharPathname,
            targetPathname: $directory . '/' . \basename($config->pharPathname),
        ));

        Task::run($config, new CopyFilesTask(
            sourceDirectory: $this->getSourceRuntimeBinDirectory(),
            targetDirectory: $directory,
        ));
    }
}
