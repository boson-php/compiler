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
        if (($sfx = $this->findCustomSfxPathname($config)) !== null) {
            return $sfx;
        }

        return match ($this->arch) {
            BuiltinArchitectureTarget::Amd64 => match (true) {
                $this->isExtensionMatches($config, self::MINIMAL_SFX_EXTENSIONS)
                    => __DIR__ . '/../../bin/minimal/windows-x86_64.sfx',
                $this->isExtensionMatches($config, self::STANDARD_SFX_EXTENSIONS)
                    => __DIR__ . '/../../bin/standard/windows-x86_64.sfx',
                default => throw $this->missingExtensionsError($config),
            },
            default => throw $this->unsupportedArchitectureOfPlatform(
                platform: BuiltinPlatformTarget::Windows,
                arch: $this->arch,
            ),
        };
    }

    private function missingExtensionsError(Configuration $config): \Throwable
    {
        $missing = \implode(', ', $this->getMissingDependencies(
            config: $config,
            actual: self::STANDARD_SFX_EXTENSIONS,
        ));

        $expected = \implode(', ', $this->getExpectedDependencies($config));

        return new \RuntimeException(\sprintf(
            <<<'MESSAGE'
                An expected [%s] extensions not supported by this compile target, please add it manually:
                1) Fork this repository: https://github.com/boson-php/backend-src
                2) Open GitHub Actions: https://github.com/USERNAME/backend-src/actions/workflows/build-windows-x86_64.yml
                3) Press "Run workflow" dropdown
                3.1) Disable "build cli binary" checkbox
                3.2) Enable "build phpmicro binary" checkbox
                3.3) Insert "%s" into "extensions to compile" text input
                4) Press "Run workflow" dropdown button
                5) Download compiled SFX assembly
                6) Add SFX assembly to "sfx" configuration section of this compile target
                MESSAGE,
            $missing,
            $expected,
        ));
    }
}
