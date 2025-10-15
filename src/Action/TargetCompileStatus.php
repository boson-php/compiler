<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

enum TargetCompileStatus
{
    case ReadyToCompile;
    case Progress;
    case Compiled;
}
