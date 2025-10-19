<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Step\Context;

readonly class GlobalContext extends Context
{
    public function __toString(): string
    {
        return '{main}';
    }
}
