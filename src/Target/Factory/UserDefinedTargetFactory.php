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
        if (!$this->isSupportedType($input['type'])) {
            return null;
        }

        $instance = $this->createUserFactory($input['type'], $config);

        return $instance->create($input, $config);
    }

    /**
     * @param CompilationTargetConfigType $input
     */
    protected function createUserFactory(array $input, Configuration $config): TargetFactoryInterface
    {
        $factory = $input['type'];

        try {
            return new $factory();
        } catch (\Throwable $e) {
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
