<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Configuration\Factory\JsonConfiguration;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory;
use Boson\Component\Compiler\Target\Factory\PharTargetFactory;
use Boson\Component\Compiler\Target\Factory\SelectiveTargetFactory;
use Boson\Component\Compiler\Target\Factory\UserDefinedTargetFactory;
use Boson\Component\Compiler\Target\TargetFactoryInterface;
use Boson\Component\Compiler\Target\TargetInterface;

/**
 * @phpstan-import-type CompilationTargetConfigType from TargetFactoryInterface
 */
final readonly class JsonConfigTargetFactory extends SelectiveTargetFactory
{
    /**
     * @param iterable<mixed, TargetFactoryInterface>|null $factories
     */
    public function __construct(iterable $factories = [
        new BuiltinTargetFactory(),
        new PharTargetFactory(),
        new UserDefinedTargetFactory(),
    ])
    {
        parent::__construct($factories);
    }

    public function create(array $input, Configuration $config): TargetInterface
    {
        return parent::create($input, $config)
            ?? throw $this->invalidTargetError($input);
    }

    /**
     * @param CompilationTargetConfigType $input
     */
    private function invalidTargetError(array $input): \Throwable
    {
        return new \InvalidArgumentException(\sprintf(
            'Could not create compilation target of type "%s"',
            $input['type'] ?? '<unknown>',
        ));
    }
}
