<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Configuration;

interface TargetInterface extends \Stringable
{
    /**
     * Gets target type
     *
     * @var non-empty-string
     */
    public string $type {
        get;
    }

    /**
     * Gets output directory
     *
     * @var non-empty-string
     */
    public string $output {
        get;
    }

    /**
     * Gets target additional configuration
     *
     * @var array<array-key, mixed>
     */
    public array $config {
        get;
    }

    /**
     * @return iterable<string|\Stringable, \UnitEnum>
     */
    public function compile(Configuration $config): iterable;
}
