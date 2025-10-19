<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Task;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\Support\Path;

final readonly class CreateDirectoryTask implements TaskInterface
{
    /**
     * @var non-empty-string
     */
    public string $directory;

    /**
     * @param non-empty-string $directory
     */
    public function __construct(
        string $directory,
        public bool $writable = true,
    ) {
        $this->directory = Path::normalize($directory);
    }

    private function createDirectoryOrFail(): void
    {
        $isCreated = @\mkdir($this->directory, recursive: true);

        if ($isCreated) {
            return;
        }

        throw new \RuntimeException(\sprintf(
            'Could not create directory "%s"',
            $this->directory,
        ));
    }

    public function __invoke(Configuration $config): void
    {
        Task::info('Create "%s" directory', [
            Path::simplify($config, $this->directory),
        ]);

        if (!\is_dir($this->directory)) {
            $this->createDirectoryOrFail();
        }

        if ($this->writable) {
            Task::run($config, new ApplyPermissionsTask($this->directory));
        }

        Task::notify('Directory has been created');
    }
}
