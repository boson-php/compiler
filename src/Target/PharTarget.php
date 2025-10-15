<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Configuration;

final readonly class PharTarget extends Target
{
    protected function process(Configuration $config): iterable
    {
        yield $this->validatePharArchive($config);
        yield $output = $this->getAndValidateBuildDirectory($config);
        yield $this->copyArchive($config, $output);
        yield from $this->copyRuntimeBinaries($output);
    }

    private function copyRuntimeBinaries(string $output): iterable
    {
        foreach ($this->getSourceRuntimeBinaries() as $sourceBinary) {
            $targetBinary = $output . \DIRECTORY_SEPARATOR . \basename($sourceBinary);

            yield $this->copyOrFail($sourceBinary, $targetBinary);
        }
    }

    /**
     * @return iterable<array-key, non-empty-string>
     */
    private function getSourceRuntimeBinaries(): iterable
    {
        $binaries = new \DirectoryIterator($this->getSourceRuntimeBinDirectory());

        /** @var \SplFileInfo $binary */
        foreach ($binaries as $binary) {
            if ($binary->isDot() || $binary->isDir()) {
                continue;
            }

            /** @var non-empty-string */
            yield $binary->getPathname();
        }
    }

    private function copyArchive(Configuration $config, string $output): true
    {
        $targetPharPathname = $output . \DIRECTORY_SEPARATOR . \basename($config->pharPathname);

        return $this->copyOrFail($config->pharPathname, $targetPharPathname);
    }

    private function validatePharArchive(Configuration $config): true
    {
        if (\is_readable($config->pharPathname)) {
            return true;
        }

        throw new \RuntimeException(\sprintf(
            'Application archive "%s" is not available',
            $config->pharPathname,
        ));
    }
}
