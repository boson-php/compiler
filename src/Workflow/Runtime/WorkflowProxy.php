<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Runtime;

/**
 * @template TWorkflow of object
 *
 * @mixin TWorkflow
 */
final readonly class WorkflowProxy
{
    /**
     * @var WorkflowExecutor<TWorkflow>
     */
    private WorkflowExecutor $executor;

    /**
     * @param TWorkflow $context
     */
    public function __construct(object $context)
    {
        $this->executor = new WorkflowExecutor($context);
    }

    /**
     * @param non-empty-string $name
     * @param array<array-key, mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->executor->execute($name, $arguments);
    }

    public function __invoke(mixed ...$arguments): mixed
    {
        return $this->executor->execute('__invoke', $arguments);
    }
}
