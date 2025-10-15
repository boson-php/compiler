<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target\Factory;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\PharTarget;
use Boson\Component\Compiler\Target\TargetFactoryInterface;

/**
 * @phpstan-import-type CompilationTargetConfigType from TargetFactoryInterface
 */
readonly class PharTargetFactory implements TargetFactoryInterface
{
    /**
     * @var non-empty-lowercase-string
     */
    final public const string DEFAULT_TYPE = 'phar';

    /**
     * @var non-empty-lowercase-string
     */
    final public const string DEFAULT_OUTPUT = self::DEFAULT_TYPE;

    /**
     * @var non-empty-list<non-empty-lowercase-string>
     */
    final public const array AVAILABLE_TYPES = [
        self::DEFAULT_TYPE,
        'archive',
    ];

    /**
     * @var non-empty-list<non-empty-lowercase-string>
     */
    protected array $availableTypes;

    /**
     * @param iterable<mixed, non-empty-lowercase-string> $availableTypes
     */
    public function __construct(
        iterable $availableTypes = self::AVAILABLE_TYPES,
    ) {
        $availableTypes = \iterator_to_array($availableTypes, false);

        if (\count($availableTypes) === 0) {
            $availableTypes = self::AVAILABLE_TYPES;
        }

        $this->availableTypes = $availableTypes;
    }

    protected function isSupportedType(string $type): bool
    {
        return \in_array(\strtolower($type), $this->availableTypes, true);
    }

    public function create(array $input, Configuration $config): ?PharTarget
    {
        if (!$this->isSupportedType($input['type'])) {
            return null;
        }

        return new PharTarget(
            type: $this->createType($input, $config),
            output: $this->createOutput($input, $config),
            config: $input,
        );
    }

    /**
     * @param CompilationTargetConfigType $input
     *
     * @return non-empty-string
     */
    protected function createType(array $input, Configuration $config): string
    {
        return self::DEFAULT_TYPE;
    }

    /**
     * @param CompilationTargetConfigType $input
     *
     * @return non-empty-string
     */
    protected function createOutput(array $input, Configuration $config): string
    {
        return $input['output'] ?? self::DEFAULT_OUTPUT;
    }
}
