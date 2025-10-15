<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Boson\Component\Compiler\Configuration;

/**
 * @template-extends TargetAction<ValidationPharStatus>
 */
final readonly class ValidatePharAction extends TargetAction
{
    public function process(Configuration $config): iterable
    {
        yield $this->target => ValidationPharStatus::ReadyToValidate;

        if (!\is_readable($config->pharPathname)) {
            throw new \RuntimeException(\sprintf(
                'Application archive "%s" is not available',
                $config->pharPathname,
            ));
        }

        yield $this->target => ValidationPharStatus::Compiled;
    }
}
