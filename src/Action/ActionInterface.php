<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Boson\Component\Compiler\Configuration;

/**
 * @template TStatus of \UnitEnum
 * @template TKey of mixed = mixed
 */
interface ActionInterface
{
    /**
     * @return iterable<TKey, TStatus>
     */
    public function process(Configuration $config): iterable;
}
