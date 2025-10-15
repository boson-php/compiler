<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Configuration\Factory\JsonConfiguration\JsonConfigLoader;

final readonly class DecodedJsonConfigInfo
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $pathname,
        /**
         * @var array<array-key, mixed>
         */
        public array $data,
    ) {}
}
