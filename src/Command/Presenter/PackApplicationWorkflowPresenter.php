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

    private function write(SymfonyStyle $style, ProgressBar $progress, string $message): void
    {
        if ($style->isDebug()) {
            $style->writeln($message);

            return;
        }

        $progress->setMessage($message);
        $progress->advance();
    }

    private function createProgress(SymfonyStyle $style): ProgressBar
    {
        $progress = new ProgressBar($style);
        $progress->clear();
        $progress->setFormat('   [%bar%] %message%');
        $progress->clear();

        return $progress;
    }

    public function process(Configuration $config, SymfonyStyle $style): void
    {
        $progress = $this->createProgress($style);

        /** @var string|\Stringable $data */
        foreach ($this->workflow->process($config) as $data => $process) {
            switch ($process) {
                case CreateBuildDirectoryStatus::ReadyToCreate:
                    $this->write($style, $progress, ' · Checking build directory');
                    break;

                case CreateBuildDirectoryStatus::Created:
                    $this->write($style, $progress, \sprintf(
                        '   <info>●</info> Build directory "<comment>%s</comment>" is available',
                        $config->output,
                    ));
                    break;

                case CreateBoxConfigStatus::ReadyToCreate:
                    $this->write($style, $progress, ' · Checking box config');
                    break;

                case CreateBoxConfigStatus::Created:
                    $this->write($style, $progress, \sprintf(
                        '   <info>●</info> Config "<comment>%s</comment>" is created',
                        $config->boxConfigPathname,
                    ));
                    break;

                case DownloadBoxStatus::ReadyToDownload:
                    $this->write($style, $progress, ' · Checking <comment>humbug/box</comment> installation');
                    break;

                case DownloadBoxStatus::Downloading:
                    $this->write($style, $progress, 'Downloading <comment>humbug/box</comment>');
                    break;

                case DownloadBoxStatus::Complete:
                    $this->write($style, $progress, \sprintf(
                        '   <info>●</info> The "<comment>humbug/box</comment>" <info>v%s</info> is ready',
                        $config->boxVersion,
                    ));
                    break;

                case PackBoxStatus::ReadyToPack:
                    $this->write($style, $progress, ' · Pack an application');
                    break;

                case PackBoxStatus::Packing:
                    $this->write($style, $progress, (string) $data);
                    break;

                case PackBoxStatus::Packed:
                    $this->write($style, $progress, \sprintf(
                        '   <info>●</info> Application packed "<comment>%s</comment>"',
                        $config->pharPathname,
                    ));
                    break;
            }
        }
    }
}
