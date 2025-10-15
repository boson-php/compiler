<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\TargetInterface;

final readonly class CopyRuntimeBinaryAction extends CopyRuntimeAction
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        private string $binary,
        TargetInterface $target,
    ) {
        parent::__construct($target);
    }

    public function process(Configuration $config): iterable
    {
        yield $this->target => CopyStatus::ReadyToCopy;

        $runtimeSourcePathname = $this->getSourceRuntimeBinDirectory()
            . \DIRECTORY_SEPARATOR
            . $this->binary;

        $runtimeTargetPathname = $this->getBuildDirectory($config)
            . \DIRECTORY_SEPARATOR
            . $this->binary;

        $this->copyOrFail($runtimeSourcePathname, $runtimeTargetPathname);

        yield $this->target => CopyStatus::Completed;
    }
}
