<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Validator;

/**
 * @template TSchema of array<array-key, mixed>
 *
 * @template-extends ValidatorInterface<TSchema>
 */
interface FileValidatorInterface extends ValidatorInterface
{
    /**
     * @phpstan-assert TSchema $data
     *
     * @param array<array-key, mixed> $data
     * @param non-empty-string|null $pathname
     */
    public function validateOrFail(array $data, ?string $pathname = null): void;
}
