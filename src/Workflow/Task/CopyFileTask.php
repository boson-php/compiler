<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Task;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\Support\Path;

final readonly class CopyFileTask implements TaskInterface
{
    /**
     * @var non-empty-string
     */
    public string $sourcePathname;

    /**
     * @var non-empty-string
     */
    public string $targetPathname;

    /**
     * @param non-empty-string $sourcePathname
     * @param non-empty-string $targetPathname
     */
    public function __construct(
        string $sourcePathname,
        string $targetPathname,
    ) {
        $this->targetPathname = Path::normalize($targetPathname);
        $this->sourcePathname = Path::normalize($sourcePathname);
    }

    public function __invoke(Configuration $config): void
    {
        Task::info('Copy %s to %s', [
            Path::simplify($config, $this->sourcePathname),
            Path::simplify($config, $this->targetPathname),
        ]);

        $isCopied = @\copy($this->sourcePathname, $this->targetPathname);

        if ($isCopied) {
            return;
        }

        throw new \RuntimeException(\sprintf(
            'Unable to copy %s to %s',
            $this->sourcePathname,
            $this->targetPathname,
        ));
    }
}
