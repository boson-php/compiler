<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinArchitectureTarget;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinPlatformTarget;

final readonly class WindowsBuiltinTarget extends BuiltinTarget
{
    /**
     * @var non-empty-string
     */
    private const string DEFAULT_RUNTIME_BINARY_NAME = 'libboson-windows-x86_64.dll';

    protected function getTargetFilename(Configuration $config): string
    {
        return $config->name . '.exe';
    }

    protected function getRuntimeBinaryFilename(): string
    {
        return self::DEFAULT_RUNTIME_BINARY_NAME;
    }

    protected function getSfxArchivePathname(Configuration $config): string
    {
        return match ($this->arch) {
            BuiltinArchitectureTarget::Amd64 => __DIR__ . '/../../bin/minimal/windows-x86_64.sfx',
            default => throw $this->unsupportedArchitectureOfPlatform(
                platform: BuiltinPlatformTarget::Windows,
                arch: $this->arch,
            ),
        };
    }
}
