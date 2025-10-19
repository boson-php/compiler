<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Configuration;
use Composer\InstalledVersions;

abstract readonly class Target implements TargetInterface
{
    /**
     * @var non-empty-string
     */
    private const string RUNTIME_PACKAGE_NAME = 'boson-php/saucer';

    /**
     * @var non-empty-string
     */
    private const string RUNTIME_BIN_DIRECTORY = 'bin';

    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $type,
        /**
         * @var non-empty-string
         */
        public string $output,
        /**
         * @var array<array-key, mixed>
         */
        public array $config,
    ) {}

    /**
     * @return non-empty-string
     */
    protected function getSourceRuntimeBinDirectory(): string
    {
        return InstalledVersions::getInstallPath(self::RUNTIME_PACKAGE_NAME)
            . \DIRECTORY_SEPARATOR
            . self::RUNTIME_BIN_DIRECTORY;
    }

    public function getBuildDirectory(Configuration $config): string
    {
        return $config->output
            . \DIRECTORY_SEPARATOR
            . $this->output;
    }

    public function __toString(): string
    {
        return $this->type;
    }
}
