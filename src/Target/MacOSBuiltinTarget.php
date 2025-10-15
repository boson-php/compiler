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
        return self::DEFAULT_RUNTIME_BINARY_NAME;
    }

    protected function getSfxArchivePathname(Configuration $config): string
    {
        if (($sfx = $this->findCustomSfxPathname($config)) !== null) {
            return $sfx;
        }

        return match ($this->arch) {
            BuiltinArchitectureTarget::Amd64 => match (true) {
                $this->isExtensionMatches($config, self::MINIMAL_SFX_EXTENSIONS)
                    => __DIR__ . '/../../bin/minimal/macos-x86_64.sfx',
                $this->isExtensionMatches($config, self::STANDARD_SFX_EXTENSIONS)
                    => __DIR__ . '/../../bin/standard/macos-x86_64.sfx',
                default => throw $this->missingExtensionsError(
                    config: $config,
                    actual: self::STANDARD_SFX_EXTENSIONS,
                    platform: 'macos-x86_64',
                ),
            },
            /** @phpstan-ignore-next-line : Allow invalid architecture arm */
            BuiltinArchitectureTarget::Arm64 => match (true) {
                $this->isExtensionMatches($config, self::MINIMAL_SFX_EXTENSIONS)
                    => __DIR__ . '/../../bin/minimal/macos-aarch64.sfx',
                $this->isExtensionMatches($config, self::STANDARD_SFX_EXTENSIONS)
                    => __DIR__ . '/../../bin/standard/macos-aarch64.sfx',
                default => throw $this->missingExtensionsError(
                    config: $config,
                    actual: self::STANDARD_SFX_EXTENSIONS,
                    platform: 'macos-aarch64',
                ),
            },
            default => throw $this->unsupportedArchitectureOfPlatform(
                platform: BuiltinPlatformTarget::MacOS,
                arch: $this->arch,
            ),
        };
    }
}
