<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Task;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\TargetInterface;
use Boson\Component\Compiler\Workflow\Task;

final readonly class CompileTargetTask implements TaskInterface
{
    public function __construct(
        private TargetInterface $target,
    ) {}

    public function __invoke(Configuration $config): void
    {
        $buildDirectory = $this->target->getBuildDirectory($config);

        Task::run($config, new DeleteDirectoryTask(
            directory: $buildDirectory,
        ));

        Task::run($config, new CreateDirectoryTask(
            directory: $buildDirectory,
        ));

        $this->target->compile($config);

        foreach ($config->mount as $mount) {
            $sourcePathname = $config->root . '/' . $mount;
            $targetPathname = $buildDirectory . '/' . $mount;

            if (\is_file($sourcePathname)) {
                Task::run($config, new CopyFileTask(
                    sourcePathname: $sourcePathname,
                    targetPathname: $targetPathname,
                ));

                continue;
            }

            Task::run($config, new CopyFilesTask(
                sourceDirectory: $sourcePathname,
                targetDirectory: $targetPathname,
            ));
        }
    }
}
