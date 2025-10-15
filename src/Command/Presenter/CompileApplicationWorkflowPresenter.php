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

    public function process(Configuration $config, SymfonyStyle $style): void
    {
        $progress = new ProgressBar($style);
        $progress->setFormat('[%bar%] %message%');

        /** @var \Stringable|string $data */
        foreach ($this->workflow->process($config) as $data => $status) {
            switch ($status) {
                case ClearBuildAssemblyDirectoryStatus::ReadyToClean:
                    $progress->setMessage(\sprintf(
                        '[<comment>%s</comment>] Cleanup build directory',
                        $data,
                    ));
                    break;

                case ClearBuildAssemblyDirectoryStatus::Cleaning:
                    $progress->setMessage(\sprintf(
                        'Removing \"<comment>%s</comment>\"',
                        $data,
                    ));
                    break;

                case ClearBuildAssemblyDirectoryStatus::Cleaned:
                    $progress->setMessage(\sprintf(
                        '[<comment>%s</comment>] Build directory is cleaned',
                        $data,
                    ));
                    break;

                case CreateBuildAssemblyDirectoryStatus::ReadyToCreate:
                    $progress->setMessage(\sprintf(
                        '[<comment>%s</comment>] Prepare build directory',
                        $data,
                    ));
                    break;

                case CreateBuildAssemblyDirectoryStatus::Created:
                    $progress->setMessage(\sprintf(
                        '[<comment>%s</comment>] Build directory is available',
                        $data,
                    ));
                    break;

                case CompileStatus::ReadyToCompile:
                case CompileStatus::Progress:
                    $progress->setMessage(\sprintf(
                        '[<comment>%s</comment>] Building assembly',
                        $data,
                    ));
                    break;

                case CompileStatus::Compiled:
                    $progress->setMessage(\sprintf(
                        '[<comment>%s</comment>] Assembly compiled',
                        $data,
                    ));
                    break;

                case TargetCompileStatus::ReadyToCompile:
                    $progress = new ProgressBar($style);
                    $progress->setFormat('[%bar%] %message%');
                    $progress->setMessage(\sprintf(
                        '[<comment>%s</comment>] Building',
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
                        '   [<comment>%s</comment>] Target compiled',
                        $data,
                    ));
                    break;

                case CopyStatus::ReadyToCopy:
                    $progress->setMessage(\sprintf(
                        '[<comment>%s</comment>] Copy dependencies',
                        $data,
                    ));
                    break;

                case CopyStatus::Completed:
                    $progress->setMessage(\sprintf(
                        '[<comment>%s</comment>] Dependencies are copied',
                        $data,
                    ));
                    break;

                case ApplyExecutePermissionsStatus::ReadyToApply:
                    $progress->setMessage(\sprintf(
                        '[<comment>%s</comment>] Apply executable permissions',
                        $data,
                    ));
                    break;

                case ApplyExecutePermissionsStatus::Applied:
                    $progress->setMessage(\sprintf(
                        '[<comment>%s</comment>] Executable permissions are applied',
                        $data,
                    ));
                    break;
            }
        }

        $progress->clear();
    }
}
