<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Task;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\Support\Path;

/**
 * @template-implements TaskInterface<string>
 */
final readonly class ReadFileTask implements TaskInterface
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

    #[\NoDiscard]
    public function __invoke(Configuration $config): string
    {
        Task::info('Read "%s" file', [
            Path::simplify($config, $this->pathname),
        ]);

        return $this->readOrFail();
    }

    private function readOrFail(): string
    {
        $result = @\file_get_contents($this->pathname);

        if ($result !== false) {
            return $result;
        }

        throw new \RuntimeException(\sprintf(
            'Could not read file "%s"',
            $this->pathname,
        ));
    }
}
