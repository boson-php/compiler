<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

enum CopyStatus
{
    case ReadyToCopy;
    case Progress;
    case Completed;
}
