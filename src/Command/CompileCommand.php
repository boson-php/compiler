<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Command;

use Boson\Component\Compiler\Command\Presenter\CompileApplicationWorkflowPresenter;
use Boson\Component\Compiler\Command\Presenter\PackApplicationWorkflowPresenter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CompileCommand extends ConfigAwareCommand
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->getConfiguration($input);

        $style = new SymfonyStyle($input, $output);

        // ---------------------------------------------------------------------
        //  Pack Workflow
        // ---------------------------------------------------------------------
        if ($input->getOption('no-pack') !== true) {
            $pack = new PackApplicationWorkflowPresenter();

            try {
                $pack->process($config, $style);
            } catch (\Throwable $e) {
                return $this->fail($output, $e);
            }
        } else {
            $output->writeln(\sprintf(
                ' · Use an existing "<comment>%s</comment>" build',
                $config->pharPathname,
            ));
        }

        $output->writeln(\sprintf(
            ' · Build an application in "<comment>%s</comment>"',
            $config->output,
        ));

        $compile = new CompileApplicationWorkflowPresenter();

        try {
            $compile->process($config, $style);
        } catch (\Throwable $e) {
            return $this->fail($output, $e);
        }

        return self::SUCCESS;
    }
}
