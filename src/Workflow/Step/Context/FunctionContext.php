<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Step\Context;

readonly class FunctionContext extends Context
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $function,
    ) {}

    public function __toString(): string
    {
        return $this->function . '()';
    }
}
