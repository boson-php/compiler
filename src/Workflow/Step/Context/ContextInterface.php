<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Step\Context;

interface ContextInterface extends \Stringable
{
    /**
     * @return non-empty-string
     */
    public function __toString(): string;
}
