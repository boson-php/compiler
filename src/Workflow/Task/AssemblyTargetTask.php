<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Task;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\TargetInterface;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\Support\Path;

final readonly class AssemblyTargetTask implements TaskInterface
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $sfxPathname,
        /**
         * @var non-empty-string
         */
        public string $targetPathname,
        public TargetInterface $target,
    ) {}

    public function __invoke(Configuration $config): void
    {
        Task::info('Assembly target %s', [$this->target->output]);

        $targetStream = @\fopen($this->targetPathname, 'wb+');

        Task::notify('Created output file %s', [
            $this->targetPathname,
        ]);

        if ($targetStream === false) {
            throw new \RuntimeException(\sprintf(
                'Unable to create target binary "%s"',
                $this->targetPathname,
            ));
        }

        \flock($targetStream, \LOCK_EX);

        $this->appendSfxArchive($targetStream, $config);
        $this->appendPhpConfig($targetStream, $config);
        $this->appendSource($targetStream, $config);

        \flock($targetStream, \LOCK_UN);
        \fclose($targetStream);
    }

    /**
     * @param resource $stream
     *
     * @throws \Throwable
     */
    private function appendSource(mixed $stream, Configuration $config): void
    {
        Task::notify('Append %s application sources', [
            Path::simplify($config, $config->pharPathname),
        ]);

        $sourceStream = @\fopen($config->pharPathname, 'rb');

        if ($sourceStream === false) {
            throw new \RuntimeException(\sprintf(
                'Unable to open application phar file "%s"',
                $config->pharPathname,
            ));
        }

        \flock($sourceStream, \LOCK_SH);
        \stream_copy_to_stream($sourceStream, $stream);
        \fclose($sourceStream);

        Task::notify('Application sources has been loaded', [
            Path::simplify($config, $config->pharPathname),
        ]);
    }

    /**
     * @param resource $stream
     *
     * @throws \Throwable
     */
    private function appendPhpConfig(mixed $stream, Configuration $config): string
    {
        Task::notify('Configure interpreter');

        $ini = Task::run($config, new CollectInterpreterConfigTask(
            target: $this->target,
        ));

        \fwrite($stream, "\xfd\xf6\x69\xe6");
        \fwrite($stream, \pack('N', \strlen($ini)));
        \fwrite($stream, $ini);

        Task::notify('Interpreter has been configured');

        return $ini;
    }

    /**
     * @param resource $stream
     *
     * @throws \Throwable
     */
    private function appendSfxArchive(mixed $stream, Configuration $config): void
    {
        Task::notify('Write %s SFX prefix', [
            Path::simplify($config, $this->sfxPathname),
        ]);

        $archiveStream = @\fopen($this->sfxPathname, 'rb');

        if ($archiveStream === false) {
            throw new \RuntimeException(\sprintf(
                'Unable to open application SFX file "%s"',
                $this->sfxPathname,
            ));
        }

        \flock($archiveStream, \LOCK_SH);
        \stream_copy_to_stream($archiveStream, $stream);
        \fclose($archiveStream);

        Task::notify(' prefix has been written', [
            Path::simplify($config, $this->sfxPathname),
        ]);
    }
}
