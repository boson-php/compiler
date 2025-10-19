<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Task\CompileTargetTask;
use Boson\Component\Compiler\Workflow\Task\TaskInterface;

final readonly class CompileWorkflow implements TaskInterface
{
    public function __construct(
        private bool $pack,
    ) {}

    public function __invoke(Configuration $config): void
    {
        if ($this->pack) {
            Task::run($config, new PackWorkflow());
        }

        foreach ($config->targets as $target) {
            Task::info('[%s] Build target', [$target->output]);

            Task::run($config, new CompileTargetTask($target));
        }
    }
}
