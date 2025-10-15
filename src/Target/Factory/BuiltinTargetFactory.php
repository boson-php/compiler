<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target\Factory;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\BuiltinTarget;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinArchitectureTarget;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinPlatformTarget;
use Boson\Component\Compiler\Target\LinuxBuiltinTarget;
use Boson\Component\Compiler\Target\MacOsBuiltinTarget;
use Boson\Component\Compiler\Target\TargetFactoryInterface;
use Boson\Component\Compiler\Target\WindowsBuiltinTarget;

/**
 * @phpstan-import-type CompilationTargetConfigType from TargetFactoryInterface
 */
readonly class BuiltinTargetFactory implements TargetFactoryInterface
{
    public function create(array $input, Configuration $config): ?BuiltinTarget
    {
        $platform = BuiltinPlatformTarget::tryFromNormalized($input['type']);

        if ($platform === null) {
            return null;
        }

        return $this->createFromPlatform($platform, $input);
    }

    /**
     * @param CompilationTargetConfigType $input
     */
    private function createFromPlatform(
        BuiltinPlatformTarget $platform,
        array $input,
    ): BuiltinTarget {
        return $this->createFromPlatformAndArchitecture(
            platform: $platform,
            arch: $this->getArchitectureFromInput($platform, $input),
            input: $input,
        );
    }

    /**
     * @param CompilationTargetConfigType $input
     */
    private function createFromPlatformAndArchitecture(
        BuiltinPlatformTarget $platform,
        BuiltinArchitectureTarget $arch,
        array $input,
    ): BuiltinTarget {
        return match ($platform) {
            BuiltinPlatformTarget::Windows => new WindowsBuiltinTarget(
                arch: $arch,
                type: $platform->value,
                output: $input['output'] ?? null,
                config: $input,
            ),
            BuiltinPlatformTarget::Linux => new LinuxBuiltinTarget(
                arch: $arch,
                type: $platform->value,
                output: $input['output'] ?? null,
                config: $input,
            ),
            BuiltinPlatformTarget::MacOS => new MacOsBuiltinTarget(
                arch: $arch,
                type: $platform->value,
                output: $input['output'] ?? null,
                config: $input,
            ),
        };
    }

    /**
     * @param CompilationTargetConfigType $input
     */
    private function getArchitectureFromInput(BuiltinPlatformTarget $platform, array $input): BuiltinArchitectureTarget
    {
        if (!isset($input['arch'])) {
            return $this->getDefaultArchitecture();
        }

        if (!\is_string($input['arch'])) {
            throw $this->invalidArchitectureError($platform, $input['arch']);
        }

        return $this->getArchitectureFromString($platform, $input['arch']);
    }

    private function invalidArchitectureError(BuiltinPlatformTarget $platform, mixed $arch): \Throwable
    {
        return new \InvalidArgumentException(\sprintf(
            'An architecture of %s compilation target must be a string, %s given',
            $platform->value,
            \get_debug_type($arch),
        ));
    }

    /**
     * @throws \Throwable
     */
    private function getArchitectureFromString(BuiltinPlatformTarget $platform, string $arch): BuiltinArchitectureTarget
    {
        return BuiltinArchitectureTarget::tryFromNormalized($arch)
            ?? throw $this->invalidArchitectureOfPlatformError($platform, $arch);
    }

    private function invalidArchitectureOfPlatformError(BuiltinPlatformTarget $platform, string $arch): \Throwable
    {
        return new \InvalidArgumentException(\sprintf(
            'An architecture "%s" of %s compilation target is invalid, one of [%s] expected',
            $arch,
            $platform->value,
            \implode(', ', $this->getSupportedArchitectureValues()),
        ));
    }

    /**
     * @return non-empty-list<non-empty-string>
     */
    public function getSupportedArchitectureValues(): array
    {
        $result = [];

        foreach (BuiltinArchitectureTarget::cases() as $arch) {
            $result[] = $arch->value;
        }

        return $result;
    }

    public function getDefaultArchitecture(): BuiltinArchitectureTarget
    {
        return BuiltinArchitectureTarget::Amd64;
    }
}
