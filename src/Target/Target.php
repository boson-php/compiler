<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Action\CompileStatus;
use Boson\Component\Compiler\Configuration;
use Composer\InstalledVersions;

abstract readonly class Target implements TargetInterface
{
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
    protected function getBuildDirectory(Configuration $config): string
    {
        return $config->output
            . \DIRECTORY_SEPARATOR
            . $this->output;
    }

    /**
     * @return non-empty-string
     */
    protected function getAndValidateBuildDirectory(Configuration $config): string
    {
        $output = $this->getBuildDirectory($config);

        if (!\is_dir($output)) {
            throw new \RuntimeException(\sprintf(
                'Application target directory "%s" is not available',
                $output,
            ));
        }

        if (!\is_writable($output)) {
            throw new \RuntimeException(\sprintf(
                'Application target directory "%s" is not writable',
                $output,
            ));
        }

        return $output;
    }

    public function compile(Configuration $config): iterable
    {
        yield $this => CompileStatus::ReadyToCompile;

        foreach ($this->process($config) as $tick) {
            yield $tick => CompileStatus::Progress;
        }

        yield $this => CompileStatus::Compiled;
    }

    /**
     * @return iterable<mixed, mixed>
     */
    abstract protected function process(Configuration $config): iterable;

    /**
     * @return non-empty-string
     */
    protected function getSourceRuntimeBinDirectory(): string
    {
        return InstalledVersions::getInstallPath('boson-php/saucer')
            . \DIRECTORY_SEPARATOR . 'bin';
    }

    protected function copyOrFail(string $from, string $to): true
    {
        $status = @\copy($from, $to);

        if ($status === false) {
            throw new \RuntimeException(\sprintf(
                'Unable to copy %s to %s',
                $from,
                $to,
            ));
        }

        return true;
    }

    public function __toString(): string
    {
        return $this->type;
    }
}
