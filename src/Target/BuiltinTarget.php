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
     * A list of built-in extensions that are always included with PHP builds.
     *
     * @var non-empty-list<non-empty-lowercase-string>
     */
    private const array BUILTIN_EXTENSIONS = [
        'core',
        'date',
        'hash',
        'json',
        'pcre',
        'random',
        'reflection',
        'spl',
        'standard',
    ];

    /**
     * @var non-empty-list<non-empty-lowercase-string>
     */
    private const array REQUIRED_EXTENSIONS = [
        'ffi',
        'phar',
    ];

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
     * @return non-empty-string|null
     */
    protected function findCustomSfxPathname(Configuration $config): ?string
    {
        $sfx = $this->config['sfx'] ?? null;

        if (!isset($sfx)) {
            return null;
        }

        if (!\is_string($sfx) || $sfx === '') {
            throw new \InvalidArgumentException(\sprintf(
                'Custom SFX of %s compilation target must be a non empty string, %s given',
                $this->type,
                \get_debug_type($sfx),
            ));
        }

        if (\is_readable($sfx)) {
            return $sfx;
        }

        if (\is_readable($resolved = $config->root . \DIRECTORY_SEPARATOR . $sfx)) {
            return $resolved;
        }

        throw new \InvalidArgumentException(\sprintf(
            'Custom SFX "%s" of %s compilation target must be a valid pathname to the file',
            $sfx,
            $this->type,
        ));
    }

    /**
     * @param list<non-empty-string> $actual
     */
    protected function isExtensionMatches(Configuration $config, array $actual): bool
    {
        $actual = \array_unique($actual);

        foreach ($config->extensions as $expected) {
            $lower = \strtolower($expected);

            if (\in_array($lower, self::BUILTIN_EXTENSIONS, true)) {
                continue;
            }

            if (!\in_array($lower, $actual, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<non-empty-string> $actual
     *
     * @return list<non-empty-string>
     */
    protected function getMissingDependencies(Configuration $config, array $actual): array
    {
        $missing = \array_values(\array_diff($config->extensions, \array_unique($actual)));

        return $this->exceptBuiltinExtensions($missing);
    }

    /**
     * @return list<non-empty-string>
     */
    protected function getExpectedDependencies(Configuration $config): array
    {
        return $this->exceptBuiltinExtensions(\array_unique([
            ...self::REQUIRED_EXTENSIONS,
            ...$config->extensions,
        ]));
    }

    /**
     * @param array<mixed, non-empty-string> $extensions
     *
     * @return list<non-empty-string>
     */
    protected function exceptBuiltinExtensions(array $extensions): array
    {
        return \array_values(\array_diff($extensions, self::BUILTIN_EXTENSIONS));
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
