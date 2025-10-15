<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\TargetInterface;

/**
 * @template TStatus of \UnitEnum
 *
 * @template-implements ActionInterface<TStatus, \Stringable|string>
 */
abstract readonly class TargetAction implements ActionInterface
{
    public function __construct(
        protected TargetInterface $target,
    ) {}

    /**
     * @return non-empty-string
     */
    protected function getBuildDirectory(Configuration $config): string
    {
        return $config->output
            . \DIRECTORY_SEPARATOR
            . $this->target->output;
    }
}
