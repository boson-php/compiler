<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Action\TargetCompileStatus;
use Boson\Component\Compiler\Configuration;

abstract readonly class Target implements TargetInterface
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $type,
        /**
         * @var non-empty-string
         */
        public string $output,
        /**
         * @var array<array-key, mixed>
         */
        public array $config,
    ) {}

    /**
     * @return non-empty-string
     */
    protected function getBuildDirectory(Configuration $config): string
    {
        return $config->output
            . \DIRECTORY_SEPARATOR
            . $this->output;
    }

    public function compile(Configuration $config): iterable
    {
        yield $this => TargetCompileStatus::ReadyToCompile;

        foreach ($this->process($config) as $tick) {
            if ($tick instanceof \UnitEnum) {
                yield $this => $tick;
            }

            yield $this => TargetCompileStatus::Progress;
        }

        yield $this => TargetCompileStatus::Compiled;
    }

    /**
     * @return iterable<mixed, mixed>
     */
    abstract protected function process(Configuration $config): iterable;

    public function __toString(): string
    {
        return $this->type;
    }
}
