<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Task;

use Boson\Component\Compiler\Configuration;

/**
 * @template-covariant TResult of mixed = void
 */
interface TaskInterface
{
    /**
     * @return TResult
     */
    public function __invoke(Configuration $config);
}
