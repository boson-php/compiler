<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Step;

use Boson\Component\Compiler\Workflow\Step\Context\ContextInterface;

abstract readonly class MessageStep extends Step
{
    /**
     * @param int<0, max> $level
     */
    final public function __construct(
        /**
         * @var non-empty-string
         */
        public string $message,
        /**
         * @var list<scalar>
         */
        public array $arguments = [],
        int $level = 0,
        ?ContextInterface $context = null,
    ) {
        parent::__construct($level, $context);
    }

    public function withLevel(int $level): self
    {
        return new static(
            message: $this->message,
            arguments: $this->arguments,
            level: $level,
            context: $this->context,
        );
    }
}
