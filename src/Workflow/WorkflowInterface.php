<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow;

use Boson\Component\Compiler\Configuration;

interface WorkflowInterface
{
    /**
     * @return iterable<mixed, \UnitEnum>
     */
    public function process(Configuration $config): iterable;
}
