<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Action\CompileAction;
use Boson\Component\Compiler\Action\CopyRuntimeBinaryAction;
use Boson\Component\Compiler\Action\ValidateOutputDirectoryAction;
use Boson\Component\Compiler\Action\ValidatePharAction;
use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinArchitectureTarget;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinPlatformTarget;

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

    protected function process(Configuration $config): iterable
    {
        yield from new ValidatePharAction($this)
            ->process($config);

        yield from new ValidateOutputDirectoryAction($this)
            ->process($config);

        yield from new CompileAction(
            sfx: $this->getSfxArchivePathname($config),
            targetFilename: $this->getTargetFilename($config),
            target: $this,
        )
            ->process($config);

        yield from new CopyRuntimeBinaryAction(
            binary: $this->getRuntimeBinaryFilename(),
            target: $this,
        )
            ->process($config);
    }

    /**
     * @return non-empty-string
     */
    abstract protected function getRuntimeBinaryFilename(): string;

    protected function unsupportedArchitectureOfPlatform(
        BuiltinPlatformTarget $platform,
        BuiltinArchitectureTarget $arch,
    ): \Throwable {
        return new \InvalidArgumentException(\sprintf(
            'The %s compilation target does not support "%s" architecture',
            $platform->value,
            $arch->value,
        ));
    }

    /**
     * @return non-empty-string
     */
    abstract protected function getTargetFilename(Configuration $config): string;

    /**
     * @return non-empty-string
     */
    abstract protected function getSfxArchivePathname(Configuration $config): string;

    public function __toString(): string
    {
        return $this->output;
    }
}
