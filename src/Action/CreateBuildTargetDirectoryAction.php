<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Boson\Component\Compiler\Configuration;

/**
 * @template-extends TargetAction<CreateBuildDirectoryStatus>
 */
final readonly class CreateBuildTargetDirectoryAction extends TargetAction
{
    public function process(Configuration $config): iterable
    {
        yield $this->target => CreateBuildDirectoryStatus::ReadyToCreate;

        $directory = $this->getBuildDirectory($config);

        if (!\is_dir($directory)) {
            $this->createOrFail($directory);
        }

        yield $this->target => CreateBuildDirectoryStatus::Created;
    }

    private function createOrFail(string $directory): void
    {
        $status = @\mkdir($directory, recursive: true);

        if ($status === true) {
            return;
        }

        throw new \RuntimeException(\sprintf(
            'Could not create build directory "%s" for "%s" compilation target',
            $directory,
            $this->target->type,
        ));
    }
}
