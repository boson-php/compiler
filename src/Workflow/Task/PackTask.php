<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Task;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\Support\Path;
use Symfony\Component\Process\Process;

final readonly class PackTask implements TaskInterface
{
    /**
     * @var non-empty-list<non-empty-string>
     */
    private const array PROGRESS_CHARS = [
        '⣾', '⣽', '⣻', '⢿', '⡿', '⣟', '⣯', '⣷',
    ];

    /**
     * @var non-empty-string
     */
    private string $boxConfigPathname;

    /**
     * @var non-empty-string
     */
    private string $boxPharPathname;

    /**
     * @param non-empty-string $boxConfigPathname
     * @param non-empty-string $boxPharPathname
     */
    public function __construct(
        string $boxConfigPathname,
        string $boxPharPathname,
        private float $refreshRate = 1 / 3,
    ) {
        $this->boxConfigPathname = Path::normalize($boxConfigPathname);
        $this->boxPharPathname = Path::normalize($boxPharPathname);
    }

    public function __invoke(Configuration $config): void
    {
        Task::info('Pack an application (config: %s)', [
            Path::simplify($config, $this->boxConfigPathname),
        ]);

        $process = new Process(
            command: $this->createProcessArgs(),
            cwd: $config->root,
        );

        Task::notify('Execute "%s"', [
            \implode(' ', $this->createProcessArgs()),
        ]);

        $processStartedAt = \microtime(true);

        $process->start();

        $i = 0;

        do {
            Task::progress(\vsprintf('%s Building "%s" %s', [
                self::PROGRESS_CHARS[$i++ % \count(self::PROGRESS_CHARS)],
                $config->name . '.phar',
                $this->getCurrentBuildTime($processStartedAt),
            ]));

            \usleep((int) ($this->refreshRate * 100_000));
        } while (!$process->isTerminated());

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($this->formatErrorMessage(
                $process->getErrorOutput(),
            ));
        }

        Task::notify('Pack complete at %s', [
            $this->getCurrentBuildTime($processStartedAt),
        ]);
    }

    private function getCurrentBuildTime(float $buildStartsAt): string
    {
        $time = \microtime(true) - $buildStartsAt;

        $formatted = \number_format($time, 2, '.', '');

        return $formatted . 's';
    }

    private function formatErrorMessage(string $message): string
    {
        $prefix = \sprintf('Box Error (%s): ', $this->boxConfigPathname);

        \preg_match('/\[ERROR]([^\n]+)/isum', $message, $matches);

        if (isset($matches[1])) {
            return $prefix . \trim($matches[1]);
        }

        \preg_match('/\[_Humbug[^\n]+\n([^\n]+)/isum', $message, $matches);

        if (isset($matches[1])) {
            return $prefix . \trim($matches[1]);
        }

        return $prefix . \trim($message);
    }

    /**
     * @return list<non-empty-string>
     */
    private function createProcessArgs(): array
    {
        return [
            \PHP_BINARY,
            $this->boxPharPathname,
            'compile',
            '--config=' . $this->boxConfigPathname,
        ];
    }
}
