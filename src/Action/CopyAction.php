<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

/**
 * @template TStatus of \UnitEnum = CopyStatus
 *
 * @template-extends TargetAction<TStatus>
 */
abstract readonly class CopyAction extends TargetAction
{
    /**
     * @param non-empty-string $from
     * @param non-empty-string $to
     */
    protected function copyOrFail(string $from, string $to): void
    {
        $status = @\copy($from, $to);

        if ($status === false) {
            throw new \RuntimeException(\sprintf(
                'Unable to copy %s to %s',
                $from,
                $to,
            ));
        }
    }
}
