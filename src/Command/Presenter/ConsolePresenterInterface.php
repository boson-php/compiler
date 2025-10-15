<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Command\Presenter;

use Boson\Component\Compiler\Configuration;
use Symfony\Component\Console\Style\SymfonyStyle;

interface ConsolePresenterInterface
{
    public function process(Configuration $config, SymfonyStyle $style): void;
}
