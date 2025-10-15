<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Configuration;

/**
 * @phpstan-type CompilationTargetConfigType array{
 *     type: non-empty-string,
 *     output?: non-empty-string,
 *     ...<array-key, scalar|null|object|array<array-key, mixed>>
 * }
 */
interface TargetFactoryInterface
{
    /**
     * @param CompilationTargetConfigType $input
     */
    public function create(array $input, Configuration $config): ?TargetInterface;
}
