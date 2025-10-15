<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Composer\InstalledVersions;

/**
 * @template TStatus of \UnitEnum = CopyStatus
 *
 * @template-extends CopyAction<TStatus>
 */
abstract readonly class CopyRuntimeAction extends CopyAction
{
    /**
     * @var non-empty-string
     */
    private const string RUNTIME_PACKAGE_NAME = 'boson-php/saucer';

    /**
     * @var non-empty-string
     */
    private const string RUNTIME_BIN_DIRECTORY = 'bin';

    /**
     * @return non-empty-string
     */
    protected function getSourceRuntimeBinDirectory(): string
    {
        return InstalledVersions::getInstallPath(self::RUNTIME_PACKAGE_NAME)
            . \DIRECTORY_SEPARATOR
            . self::RUNTIME_BIN_DIRECTORY;
    }
}
