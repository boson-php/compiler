<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

enum ApplyExecutePermissionsStatus
{
    case ReadyToApply;
    case Applied;
}
