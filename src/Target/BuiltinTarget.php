<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinArchitectureTarget;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\AssemblyTargetTask;
use Boson\Component\Compiler\Workflow\Task\CopyFileTask;
use Boson\Component\Compiler\Workflow\Task\DownloadTask;
use Boson\Component\Compiler\Workflow\Task\FindCustomSfxPathnameTask;
use Boson\Component\Compiler\Workflow\Task\SelectEditionTask;

abstract readonly class BuiltinTarget extends Target
{
    /**
     * @param non-empty-string $type
     * @param non-empty-string|null $output
     * @param array<array-key, mixed> $config
     */
    public function __construct(
        public BuiltinArchitectureTarget $arch,
        string $type,
        ?string $output,
        array $config,
    ) {
        parent::__construct(
            type: $type,
            output: $output ?? $type . \DIRECTORY_SEPARATOR . $arch->value,
            config: $config,
        );
    }

    /**
     * @return non-empty-string
     */
    abstract protected function getTargetFilename(Configuration $config): string;

    /**
     * @return non-empty-string
     */
    protected function getTargetPathname(Configuration $config): string
    {
        return $this->getBuildDirectory($config)
            . '/' . $this->getTargetFilename($config);
    }

    /**
     * @return array<non-empty-string, list<non-empty-lowercase-string>>
     */
    abstract protected function getSfxExtensionMapping(): array;

    /**
     * @param non-empty-string $edition
     *
     * @return non-empty-string
     */
    abstract protected function getSfxFilename(string $edition): string;

    /**
     * @return non-empty-string
     */
    protected function getSfxPathname(Configuration $config): string
    {
        $pathname = Task::run($config, new FindCustomSfxPathnameTask($this));

        if ($pathname !== null) {
            return $pathname;
        }

        $edition = Task::run($config, new SelectEditionTask(
            extensions: $this->getSfxExtensionMapping(),
        ));

        $sfxFilename = $this->getSfxFilename($edition);

        Task::run($config, new DownloadTask(
            sourceUri: $config->sfxUri . $sfxFilename,
            targetPathname: $config->temp . '/' . $sfxFilename,
        ));

        return $config->temp . '/' . $sfxFilename;
    }

    /**
     * @return non-empty-string|null
     */
    abstract protected function getRuntimeBinaryFilename(): ?string;

    public function compile(Configuration $config): void
    {
        $runtimeBinaryFilename = $this->getRuntimeBinaryFilename();

        if ($runtimeBinaryFilename !== null) {
            Task::run($config, new CopyFileTask(
                sourcePathname: $this->getSourceRuntimeBinDirectory()
                    . '/' . $runtimeBinaryFilename,
                targetPathname: $this->getBuildDirectory($config)
                    . '/' . $runtimeBinaryFilename,
            ));
        }

        Task::run($config, new AssemblyTargetTask(
            sfxPathname: $this->getSfxPathname($config),
            targetPathname: $this->getTargetPathname($config),
            target: $this,
        ));
    }

    public function __toString(): string
    {
        return $this->output;
    }
}
