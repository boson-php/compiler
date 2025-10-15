<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Boson\Component\Compiler\Configuration;

final readonly class CopyAllRuntimeBinariesAction extends CopyRuntimeAction
{
    public function process(Configuration $config): iterable
    {
        yield $this->target => CopyStatus::ReadyToCopy;

        $output = $this->getBuildDirectory($config);

        foreach ($this->getSourceRuntimeBinaries() as $sourceBinary) {
            $targetBinary = $output
                . \DIRECTORY_SEPARATOR
                . \basename($sourceBinary);

            $this->copyOrFail($sourceBinary, $targetBinary);

            yield $this->target => CopyStatus::Progress;
        }

        yield $this->target => CopyStatus::Completed;
    }

    /**
     * @return iterable<array-key, non-empty-string>
     */
    private function getSourceRuntimeBinaries(): iterable
    {
        $binaries = new \DirectoryIterator($this->getSourceRuntimeBinDirectory());

        /** @var \DirectoryIterator $binary */
        foreach ($binaries as $binary) {
            if ($binary->isDot() || $binary->isDir()) {
                continue;
            }

            /** @phpstan-ignore-next-line : Pathname always non-empty string */
            yield $binary->getPathname();
        }
    }
}
