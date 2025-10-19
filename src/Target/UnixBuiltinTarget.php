<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\ApplyPermissionsTask;

abstract readonly class UnixBuiltinTarget extends BuiltinTarget
{
    protected function getTargetFilename(Configuration $config): string
    {
        return $config->name;
    }

    #[\Override]
    public function compile(Configuration $config): void
    {
        parent::compile($config);

        Task::run($config, new ApplyPermissionsTask(
            pathname: $this->getTargetPathname($config),
            permissions: ApplyPermissionsTask::DEFAULT_EXECUTE_PERMISSIONS,
        ));
    }
}
