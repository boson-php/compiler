<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow;

use Boson\Component\Compiler\Action\ClearBuildTargetDirectoryAction;
use Boson\Component\Compiler\Action\CreateBuildTargetDirectoryAction;
use Boson\Component\Compiler\Configuration;

final readonly class CompileApplicationWorkflow implements WorkflowInterface
{
    /**
     * @return iterable<mixed, \UnitEnum>
     */
    public function process(Configuration $config): iterable
    {
        foreach ($config->targets as $target) {
            // Clear build directory
            yield from new ClearBuildTargetDirectoryAction($target)
                ->process($config);

            // Create build directory
            yield from new CreateBuildTargetDirectoryAction($target)
                ->process($config);

            yield from $target->compile($config);
        }
    }
}
