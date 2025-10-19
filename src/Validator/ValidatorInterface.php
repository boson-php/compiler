<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Validator;

/**
 * @template TSchema of array<array-key, mixed>
 */
interface ValidatorInterface
{
    /**
     * @phpstan-assert TSchema $data
     *
     * @param array<array-key, mixed> $data
     */
    public function validateOrFail(array $data): void;
}
