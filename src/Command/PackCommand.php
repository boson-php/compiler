<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Command;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\PackWorkflow;
use Boson\Component\Compiler\Workflow\Task\TaskInterface;
use Symfony\Component\Console\Input\InputInterface;

final class PackCommand extends WorkflowCommand
{
    public function __construct(?string $name = null)
    {
        parent::__construct($name ?? 'pack');
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Pack application files to PHAR assembly');
    }

    protected function getWorkflow(InputInterface $input, Configuration $config): TaskInterface
    {
        return new PackWorkflow();
    }
}
