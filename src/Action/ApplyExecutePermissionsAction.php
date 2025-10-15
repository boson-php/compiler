<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\TargetInterface;

/**
 * @template-extends TargetAction<ApplyExecutePermissionsStatus>
 */
final readonly class ApplyExecutePermissionsAction extends TargetAction
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        private string $targetFilename,
        TargetInterface $target,
    ) {
        parent::__construct($target);
    }

    public function process(Configuration $config): iterable
    {
        yield $this->target => ApplyExecutePermissionsStatus::ReadyToApply;

        $targetPathname = $this->getBuildDirectory($config)
            . \DIRECTORY_SEPARATOR
            . $this->targetFilename;

        $status = @\chmod($targetPathname, 0o755);

        if ($status === false) {
            throw new \RuntimeException(\sprintf(
                'Unable to make %s executable',
                $targetPathname,
            ));
        }

        yield $this->target => ApplyExecutePermissionsStatus::Applied;
    }
}
