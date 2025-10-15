<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Boson\Component\Compiler\Configuration;

/**
 * @template-extends CopyAction<CopyStatus>
 */
final readonly class CopyPharAction extends CopyAction
{
    public function process(Configuration $config): iterable
    {
        yield $this->target => CopyStatus::ReadyToCopy;

        $targetPharPathname = $this->getBuildDirectory($config)
            . \DIRECTORY_SEPARATOR
            . \basename($config->pharPathname);

        $this->copyOrFail($config->pharPathname, $targetPharPathname);

        yield $this->target => CopyStatus::Completed;
    }
}
