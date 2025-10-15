<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Command\Presenter;

use Boson\Component\Compiler\Action\ApplyExecutePermissionsStatus;
use Boson\Component\Compiler\Action\ClearBuildAssemblyDirectoryStatus;
use Boson\Component\Compiler\Action\CompileStatus;
use Boson\Component\Compiler\Action\CopyStatus;
use Boson\Component\Compiler\Action\CreateBuildAssemblyDirectoryStatus;
use Boson\Component\Compiler\Action\TargetCompileStatus;
use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\CompileApplicationWorkflow;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @template-extends ConsolePresenter<CompileApplicationWorkflow>
 */
final readonly class CompileApplicationWorkflowPresenter extends ConsolePresenter
{
    public function __construct()
    {
        parent::__construct(new CompileApplicationWorkflow());
    }

    private function write(SymfonyStyle $style, ProgressBar $progress, string $message): void
    {
        if ($style->isDebug()) {
            $progress->clear();
            $style->writeln('   ' . $message);

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

        /** @var \Stringable|string $data */
        foreach ($this->workflow->process($config) as $data => $status) {
            switch ($status) {
                case ClearBuildAssemblyDirectoryStatus::ReadyToClean:
                    $this->write($style, $progress, \sprintf(
                        '[<comment>%s</comment>] · Cleanup build directory',
                        $data,
                    ));
                    break;

                case ClearBuildAssemblyDirectoryStatus::Cleaning:
                    $this->write($style, $progress, \sprintf(
                        '· remove "<comment>%s</comment>"',
                        $data,
                    ));
                    break;

                case ClearBuildAssemblyDirectoryStatus::Cleaned:
                    $progress->clear();
                    $this->write($style, $progress, \sprintf(
                        '[<comment>%s</comment>] <info>●</info> Build directory is cleaned',
                        $data,
                    ));
                    break;

                case CreateBuildAssemblyDirectoryStatus::ReadyToCreate:
                    $this->write($style, $progress, \sprintf(
                        '[<comment>%s</comment>] · Prepare build directory',
                        $data,
                    ));
                    break;

                case CreateBuildAssemblyDirectoryStatus::Created:
                    $this->write($style, $progress, \sprintf(
                        '[<comment>%s</comment>] <info>●</info> Build directory is available',
                        $data,
                    ));
                    break;

                case CompileStatus::ReadyToCompile:
                case CompileStatus::Progress:
                    $this->write($style, $progress, \sprintf(
                        '[<comment>%s</comment>] · Building assembly',
                        $data,
                    ));
                    break;

                case CompileStatus::BuildConfiguration:
                    $this->write($style, $progress, \sprintf(
                        "php.ini:\n   · %s",
                        \str_replace("\n", "\n   · ", \rtrim((string) $data)),
                    ));
                    break;

                case CompileStatus::Compiled:
                    $this->write($style, $progress, \sprintf(
                        '[<comment>%s</comment>] <info>●</info> Assembly compiled',
                        $data,
                    ));
                    break;

                case TargetCompileStatus::ReadyToCompile:
                    $progress = $this->createProgress($style);

                    $this->write($style, $progress, \sprintf(
                        '[<comment>%s</comment>] · Building',
                        $data,
                    ));
                    break;

                case TargetCompileStatus::Progress:
                    $progress->advance();
                    break;

                case TargetCompileStatus::Compiled:
                    $progress->clear();

                    \usleep(100);

                    $style->writeln(\sprintf(
                        '   [<comment>%s</comment>] <info>●</info> Target compiled',
                        $data,
                    ));
                    break;

                case CopyStatus::ReadyToCopy:
                    $this->write($style, $progress, \sprintf(
                        '[<comment>%s</comment>] · Copy new dependencies',
                        $data,
                    ));
                    break;

                case CopyStatus::Completed:
                    $this->write($style, $progress, \sprintf(
                        '[<comment>%s</comment>] <info>●</info> Dependencies are copied',
                        $data,
                    ));
                    break;

                case ApplyExecutePermissionsStatus::ReadyToApply:
                    $this->write($style, $progress, \sprintf(
                        '[<comment>%s</comment>] · Apply executable permissions',
                        $data,
                    ));
                    break;

                case ApplyExecutePermissionsStatus::Applied:
                    $this->write($style, $progress, \sprintf(
                        '[<comment>%s</comment>] <info>●</info> Executable permissions are applied',
                        $data,
                    ));
                    break;
            }
        }

        $progress->clear();
    }
}
