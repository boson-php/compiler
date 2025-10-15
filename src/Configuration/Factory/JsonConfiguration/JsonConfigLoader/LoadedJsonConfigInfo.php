<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Configuration\Factory\JsonConfiguration\JsonConfigLoader;

final readonly class LoadedJsonConfigInfo
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $pathname,
        public string $json,
    ) {}
}
