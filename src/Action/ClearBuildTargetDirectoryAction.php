<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Boson\Component\Compiler\Configuration;

/**
 * @template-extends TargetAction<ClearBuildAssemblyDirectoryStatus>
 */
final readonly class ClearBuildTargetDirectoryAction extends TargetAction
{
    public function process(Configuration $config): iterable
    {
        yield $this->target => ClearBuildAssemblyDirectoryStatus::ReadyToClean;

        $directory = $this->getBuildDirectory($config);

        if (\is_dir($directory)) {
            /** @var \SplFileInfo $file */
            foreach ($this->getIterator($directory) as $file) {
                if ($file->isDir()) {
                    yield $file->getPathname() => ClearBuildAssemblyDirectoryStatus::Cleaning;

                    \rmdir($file->getPathname());
                }

                if ($file->isFile()) {
                    yield $file->getPathname() => ClearBuildAssemblyDirectoryStatus::Cleaning;

                    \unlink($file->getPathname());
                }
            }

            yield $directory => ClearBuildAssemblyDirectoryStatus::Cleaning;

            \rmdir($directory);
        }

        yield $this->target => ClearBuildAssemblyDirectoryStatus::Cleaned;
    }

    /**
     * @return \RecursiveIteratorIterator<\RecursiveDirectoryIterator>
     */
    private function getIterator(string $directory): \RecursiveIteratorIterator
    {
        return new \RecursiveIteratorIterator(
            iterator: new \RecursiveDirectoryIterator(
                directory: $directory,
                flags: \FilesystemIterator::SKIP_DOTS,
            ),
            mode: \RecursiveIteratorIterator::CHILD_FIRST,
        );
    }
}
