<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Action\CopyAllRuntimeBinariesAction;
use Boson\Component\Compiler\Action\CopyPharAction;
use Boson\Component\Compiler\Action\ValidateOutputDirectoryAction;
use Boson\Component\Compiler\Action\ValidatePharAction;
use Boson\Component\Compiler\Configuration;

final readonly class PharTarget extends Target
{
    protected function process(Configuration $config): iterable
    {
        yield from new ValidatePharAction($this)
            ->process($config);

        yield from new ValidateOutputDirectoryAction($this)
            ->process($config);

        yield from new CopyPharAction($this)
            ->process($config);

        yield from new CopyAllRuntimeBinariesAction($this)
            ->process($config);
    }
}
