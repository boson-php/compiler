<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinArchitectureTarget;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinPlatformTarget;

final readonly class LinuxBuiltinTarget extends UnixBuiltinTarget
{
    /**
     * @var non-empty-string
     */
    private const string DEFAULT_RUNTIME_AMD64_BINARY_NAME = 'libboson-linux-x86_64.so';

    /**
     * @var non-empty-string
     */
    private const string DEFAULT_RUNTIME_ARM64_BINARY_NAME = 'libboson-linux-aarch64.so';

    protected function getRuntimeBinaryFilename(): string
    {
        return match ($this->arch) {
            BuiltinArchitectureTarget::Amd64 => self::DEFAULT_RUNTIME_AMD64_BINARY_NAME,
            BuiltinArchitectureTarget::Arm64 => self::DEFAULT_RUNTIME_ARM64_BINARY_NAME,
            default => $this->unsupportedArchitectureOfPlatform(
                platform: BuiltinPlatformTarget::Linux,
                arch: $this->arch,
            )
        };
    }

    protected function getSfxArchivePathname(Configuration $config): string
    {
        return match ($this->arch) {
            BuiltinArchitectureTarget::Amd64 => __DIR__ . '/../../bin/minimal/linux-x86_64.sfx',
            BuiltinArchitectureTarget::Arm64 => __DIR__ . '/../../bin/minimal/linux-aarch64.sfx',
            default => $this->unsupportedArchitectureOfPlatform(
                platform: BuiltinPlatformTarget::Linux,
                arch: $this->arch,
            ),
        };
    }
}
