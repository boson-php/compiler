<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Command\Presenter;

use Boson\Component\Compiler\Action\CreateBoxConfigStatus;
use Boson\Component\Compiler\Action\CreateBuildDirectoryStatus;
use Boson\Component\Compiler\Action\DownloadBoxStatus;
use Boson\Component\Compiler\Action\PackBoxStatus;
use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\PackApplicationWorkflow;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @template-extends ConsolePresenter<PackApplicationWorkflow>
 */
final readonly class PackApplicationWorkflowPresenter extends ConsolePresenter
{
    public function __construct()
    {
        parent::__construct(new PackApplicationWorkflow());
    }

    public function process(Configuration $config, SymfonyStyle $style): void
    {
        $progress = new ProgressBar($style);
        $progress->setFormat('[%bar%] %message%');

        /** @var string|\Stringable $data */
        foreach ($this->workflow->process($config) as $data => $process) {
            switch ($process) {
                case CreateBuildDirectoryStatus::ReadyToCreate:
                    $style->write(' · Checking build directory');
                    break;

                case CreateBuildDirectoryStatus::Created:
                    $style->writeln(\sprintf(
                        "\33[2K\r <info>●</info> Build directory \"<comment>%s</comment>\" is available",
                        $config->output,
                    ));
                    break;

                case CreateBoxConfigStatus::ReadyToCreate:
                    $style->write(' · Checking box config');
                    break;

                case CreateBoxConfigStatus::Created:
                    $style->writeln(\sprintf(
                        "\33[2K\r <info>●</info> Config \"<comment>%s</comment>\" is created",
                        $config->boxConfigPathname,
                    ));
                    break;

                case DownloadBoxStatus::ReadyToDownload:
                    $style->write(' · Checking <comment>humbug/box</comment> installation');
                    break;

                case DownloadBoxStatus::Downloading:
                    $progress->setMessage('Downloading <comment>humbug/box</comment>');
                    $progress->advance();
                    break;

                case DownloadBoxStatus::Complete:
                    $progress->clear();
                    $style->writeln(\sprintf(
                        "\33[2K\r <info>●</info> The \"<comment>humbug/box</comment>\" <info>v%s</info> is ready",
                        $config->boxVersion,
                    ));
                    break;

                case PackBoxStatus::ReadyToPack:
                    $style->write(' · Pack an application');
                    break;

                case PackBoxStatus::Packing:
                    $progress->setMessage("Pack an application\n" . (string) $data);
                    $progress->advance();
                    break;

                case PackBoxStatus::Packed:
                    $progress->clear();
                    $style->writeln(\sprintf(
                        "\33[2K\r <info>●</info> Application packed \"<comment>%s</comment>\"",
                        $config->pharPathname,
                    ));
                    break;
            }
        }
    }
}
