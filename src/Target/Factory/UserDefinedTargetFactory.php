<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target\Factory;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\TargetFactoryInterface;
use Boson\Component\Compiler\Target\TargetInterface;

/**
 * @phpstan-import-type CompilationTargetConfigType from TargetFactoryInterface
 */
readonly class UserDefinedTargetFactory implements TargetFactoryInterface
{
    public function create(array $input, Configuration $config): ?TargetInterface
    {
        $factory = $input['type'];

        if (!$this->isSupportedType($factory)) {
            return null;
        }

        $instance = $this->createUserFactory($factory, $input, $config);

        return $instance->create($input, $config);
    }

    /**
     * @param class-string<TargetFactoryInterface> $factory
     * @param CompilationTargetConfigType $input
     */
    protected function createUserFactory(string $factory, array $input, Configuration $config): TargetFactoryInterface
    {
        try {
            return new $factory();
        } catch (\Throwable $e) { // @phpstan-ignore-line : This is not dead catch
            throw new \RuntimeException(\sprintf(
                'Unable to create %s compilation target factory: %s',
                $factory,
                $e->getMessage(),
            ), previous: $e);
        }
    }

    /**
     * @phpstan-assert-if-true class-string<TargetFactoryInterface> $type
     */
    protected function isSupportedType(string $type): bool
    {
        return \is_subclass_of($type, TargetFactoryInterface::class, true);
    }
}
