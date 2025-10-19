<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Task;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\Support\Path;

final readonly class DeleteFileTask implements TaskInterface
{
    /**
     * @var non-empty-string
     */
    public string $pathname;

    /**
     * @param non-empty-string $pathname
     */
    public function __construct(string $pathname)
    {
        $this->pathname = Path::normalize($pathname);
    }

    public function __invoke(Configuration $config): void
    {
        Task::info('Delete "%s" file', [
            Path::simplify($config, $this->pathname),
        ]);

        $this->deleteOrFail();

        Task::notify('File has been deleted');
    }

    private function deleteOrFail(): void
    {
        $isDeleted = @\unlink($this->pathname);

        if ($isDeleted) {
            return;
        }

        throw new \RuntimeException(\sprintf(
            'Could not delete file "%s"',
            $this->pathname,
        ));
    }
}
