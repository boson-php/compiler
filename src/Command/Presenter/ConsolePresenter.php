<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Command\Presenter;

use Boson\Component\Compiler\Workflow\WorkflowInterface;

/**
 * @template TWorkflow of WorkflowInterface
 */
abstract readonly class ConsolePresenter implements ConsolePresenterInterface
{
    public function __construct(
        /**
         * @var TWorkflow
         */
        protected WorkflowInterface $workflow,
    ) {}
}
