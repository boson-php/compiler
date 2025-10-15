<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Boson\Component\Compiler\Configuration;

/**
 * @template-extends TargetAction<ValidationOutputDirectoryStatus>
 */
final readonly class ValidateOutputDirectoryAction extends TargetAction
{
    public function process(Configuration $config): iterable
    {
        yield $this->target => ValidationOutputDirectoryStatus::ReadyToValidate;

        $output = $this->getBuildDirectory($config);

        if (!\is_dir($output)) {
            throw new \RuntimeException(\sprintf(
                'Target directory "%s" is not available',
                $output,
            ));
        }

        if (!\is_writable($output)) {
            throw new \RuntimeException(\sprintf(
                'Target directory "%s" is not writable',
                $output,
            ));
        }

        yield $this->target => ValidationOutputDirectoryStatus::Compiled;
    }
}
