<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Command;

use Boson\Component\Compiler\Action\ClearBuildAssemblyDirectoryStatus;
use Boson\Component\Compiler\Action\CompileStatus;
use Boson\Component\Compiler\Action\CreateBuildAssemblyDirectoryStatus;
use Boson\Component\Compiler\Command\PackCommand\PackApplicationWorkflowPresenter;
use Boson\Component\Compiler\Workflow\CompileApplicationWorkflow;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

        // ---------------------------------------------------------------------
        //  Pack Workflow
        // ---------------------------------------------------------------------
        if ($input->getOption('no-pack') !== true) {
            $pack = new PackApplicationWorkflowPresenter();

            try {
                $pack->process($config, $output);
            } catch (\Throwable $e) {
                return $this->fail($output, $e);
            }
        } else {
            $output->writeln(\sprintf(
                ' · Use an existing "<comment>%s</comment>" build',
                $config->pharPathname,
            ));
        }

        $workflow = new CompileApplicationWorkflow();

        $output->writeln(\sprintf(
            ' · Build an application in "<comment>%s</comment>"',
            $config->output,
        ));

        /** @var \Stringable|string $data */
        foreach ($workflow->process($config) as $data => $status) {
            switch ($status) {
                case ClearBuildAssemblyDirectoryStatus::ReadyToClean:
                    $output->write(\sprintf(
                        '   [<comment>%s</comment>] Cleanup build directory...',
                        $data,
                    ));
                    break;

                case ClearBuildAssemblyDirectoryStatus::Cleaning:
                    $output->write(\sprintf(
                        "\33[2K\r   ↳ Removing \"<comment>%s</comment>\"",
                        $data,
                    ));
                    break;

                case ClearBuildAssemblyDirectoryStatus::Cleaned:
                    $output->writeln(\sprintf(
                        "\33[2K\r   [<comment>%s</comment>] Build directory is cleaned",
                        $data,
                    ));
                    break;

                case CreateBuildAssemblyDirectoryStatus::ReadyToCreate:
                    $output->write(\sprintf(
                        '   [<comment>%s</comment>] Prepare build directory',
                        $data,
                    ));
                    break;

                case CreateBuildAssemblyDirectoryStatus::Created:
                    $output->writeln(\sprintf(
                        "\33[2K\r   [<comment>%s</comment>] Build directory is available",
                        $data,
                    ));
                    break;

                case CompileStatus::ReadyToCompile:
                    $output->write(\sprintf(
                        '   [<comment>%s</comment>] Compilation...',
                        $data,
                    ));
                    break;

                case CompileStatus::Compiled:
                    $output->writeln(\sprintf(
                        "\33[2K\r   [<comment>%s</comment>] Compiled",
                        $data,
                    ));
                    break;
            }
        }

        return self::SUCCESS;
    }
}
