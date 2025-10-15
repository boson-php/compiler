<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Action\ApplyExecutePermissionsAction;
use Boson\Component\Compiler\Configuration;

abstract readonly class UnixBuiltinTarget extends BuiltinTarget
{
    protected function getTargetFilename(Configuration $config): string
    {
        return $config->name;
    }

    #[\Override]
    protected function process(Configuration $config): iterable
    {
        yield from parent::process($config);

        yield from new ApplyExecutePermissionsAction(
            targetFilename: $this->getTargetFilename($config),
            target: $this
        )
            ->process($config);
    }
}
