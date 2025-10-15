<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

enum ValidationPharStatus
{
    case ReadyToValidate;
    case Compiled;
}
