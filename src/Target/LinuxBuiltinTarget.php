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

    /**
     * @var list<non-empty-lowercase-string>
     */
    private const array MINIMAL_SFX_EXTENSIONS = [
        'core',
        'ctype',
        'date',
        'ffi',
        'hash',
        'iconv',
        'json',
        'pcre',
        'random',
        'reflection',
        'shmop',
        'spl',
        'standard',
        'zlib',
        'mbstring',
        'phar',
        'opcache',
    ];

    /**
     * @var list<non-empty-lowercase-string>
     */
    public const array STANDARD_SFX_EXTENSIONS = [
        'core',
        'ctype',
        'curl',
        'date',
        'ffi',
        'hash',
        'iconv',
        'json',
        'openssl',
        'pcre',
        'random',
        'reflection',
        'shmop',
        'sockets',
        'standard',
        'spl',
        'sqlite3',
        'sodium',
        'zlib',
        'libxml',
        'dom',
        'mbstring',
        'pdo',
        'pdo_sqlite',
        'phar',
        'xml',
        'opcache',
    ];

    protected function getRuntimeBinaryFilename(): string
    {
        return match ($this->arch) {
            BuiltinArchitectureTarget::Amd64 => self::DEFAULT_RUNTIME_AMD64_BINARY_NAME,
            /** @phpstan-ignore-next-line : Allow invalid architecture arm */
            BuiltinArchitectureTarget::Arm64 => self::DEFAULT_RUNTIME_ARM64_BINARY_NAME,
            default => throw $this->unsupportedArchitectureOfPlatform(
                platform: BuiltinPlatformTarget::Linux,
                arch: $this->arch,
            )
        };
    }

    protected function getSfxArchivePathname(Configuration $config): string
    {
        if (($sfx = $this->findCustomSfxPathname($config)) !== null) {
            return $sfx;
        }

        return match ($this->arch) {
            BuiltinArchitectureTarget::Amd64 => match (true) {
                $this->isExtensionMatches($config, self::MINIMAL_SFX_EXTENSIONS)
                    => __DIR__ . '/../../bin/minimal/linux-x86_64.sfx',
                $this->isExtensionMatches($config, self::STANDARD_SFX_EXTENSIONS)
                    => __DIR__ . '/../../bin/standard/linux-x86_64.sfx',
                default => throw $this->missingExtensionsError(
                    config: $config,
                    actual: self::STANDARD_SFX_EXTENSIONS,
                    platform: 'linux-x86_64-glibc',
                ),
            },
            /** @phpstan-ignore-next-line : Allow invalid architecture arm */
            BuiltinArchitectureTarget::Arm64 => match (true) {
                $this->isExtensionMatches($config, self::MINIMAL_SFX_EXTENSIONS)
                    => __DIR__ . '/../../bin/minimal/linux-aarch64.sfx',
                $this->isExtensionMatches($config, self::STANDARD_SFX_EXTENSIONS)
                    => __DIR__ . '/../../bin/standard/linux-aarch64.sfx',
                default => throw $this->missingExtensionsError(
                    config: $config,
                    actual: self::STANDARD_SFX_EXTENSIONS,
                    platform: 'linux-aarch64-glibc',
                ),
            },
            default => throw $this->unsupportedArchitectureOfPlatform(
                platform: BuiltinPlatformTarget::Linux,
                arch: $this->arch,
            ),
        };
    }
}
