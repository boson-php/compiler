<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinArchitectureTarget;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinPlatformTarget;

final readonly class MacOSBuiltinTarget extends UnixBuiltinTarget
{
    /**
     * @var non-empty-string
     */
    private const string DEFAULT_RUNTIME_BINARY_NAME = 'libboson-darwin-universal.dylib';

    protected function getRuntimeBinaryFilename(): string
    {
        return self::DEFAULT_RUNTIME_BINARY_NAME;
    }

    protected function getSfxArchivePathname(Configuration $config): string
    {
        return match ($this->arch) {
            BuiltinArchitectureTarget::Amd64 => __DIR__ . '/../../bin/minimal/macos-x86_64.sfx',
            /** @phpstan-ignore-next-line : Allow invalid architecture arm */
            BuiltinArchitectureTarget::Arm64 => __DIR__ . '/../../bin/minimal/macos-aarch64.sfx',
            default => throw $this->unsupportedArchitectureOfPlatform(
                platform: BuiltinPlatformTarget::MacOS,
                arch: $this->arch,
            ),
        };
    }
}
