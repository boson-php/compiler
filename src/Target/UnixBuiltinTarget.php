<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Configuration;

abstract readonly class UnixBuiltinTarget extends BuiltinTarget
{
    protected function getTargetBinary(string $output, Configuration $config): string
    {
        return $output . '/' . $config->name;
    }

    #[\Override]
    protected function build(string $target, Configuration $config): iterable
    {
        yield from parent::build($target, $config);

        yield from $this->applyExecutePermissions($target);
    }

    /**
     * @return iterable<array-key, bool>
     */
    protected function applyExecutePermissions(string $target): iterable
    {
        yield \chmod($target, 0o755);
    }
}
