<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target\Factory;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\TargetFactoryInterface;
use Boson\Component\Compiler\Target\TargetInterface;

readonly class SelectiveTargetFactory implements TargetFactoryInterface
{
    /**
     * @var list<TargetFactoryInterface>
     */
    private array $factories;

    /**
     * @param iterable<mixed, TargetFactoryInterface>|null $factories
     */
    public function __construct(iterable $factories)
    {
        $this->factories = \iterator_to_array($factories, false);
    }

    public function create(array $input, Configuration $config): ?TargetInterface
    {
        foreach ($this->factories as $factory) {
            $result = $factory->create($input, $config);

            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }
}
