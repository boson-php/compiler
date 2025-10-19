<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Command;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\CompileWorkflow;
use Boson\Component\Compiler\Workflow\Task\TaskInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class CompileCommand extends WorkflowCommand
{
    public function __construct(?string $name = null)
    {
        parent::__construct($name ?? 'compile');
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Compile application to executable binary');

        $this->addOption(
            name: 'no-pack',
            mode: InputOption::VALUE_NONE,
            description: 'Only compilation is performed without source packing',
        );
    }

    protected function getWorkflow(InputInterface $input, Configuration $config): TaskInterface
    {
        return new CompileWorkflow(
            pack: $input->getOption('no-pack') !== true,
        );
    }
}
