<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

enum ValidationOutputDirectoryStatus
{
    case ReadyToValidate;
    case Compiled;
}
