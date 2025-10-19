<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Step\Context;

readonly class MethodContext extends FunctionContext
{
    /**
     * @param non-empty-string $method
     */
    public function __construct(
        /**
         * @var class-string
         */
        public string $class,
        string $method,
    ) {
        parent::__construct($method);
    }

    public function __toString(): string
    {
        return $this->class . '::' . parent::__toString();
    }
}
