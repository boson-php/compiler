<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Task;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\Support\Path;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class DownloadTask implements TaskInterface
{
    private HttpClientInterface $client;

    /**
     * @var non-empty-string
     */
    private string $target;

    /**
     * @param non-empty-string $targetPathname
     */
    public function __construct(
        /**
         * @var non-empty-string
         */
        private string $sourceUri,
        string $targetPathname,
        ?HttpClientInterface $client = null,
    ) {
        $this->target = Path::normalize($targetPathname);
        $this->client = $client ?? HttpClient::create();
    }

    /**
     * @return resource
     */
    private function openStream(): mixed
    {
        $stream = @\fopen($this->target, 'w+b');

        if ($stream !== false) {
            return $stream;
        }

        throw new \RuntimeException(\sprintf(
            'Unable to create "%s" file for writing',
            $this->target,
        ));
    }

    /**
     * @return resource
     */
    private function openAndLockStream(): mixed
    {
        $stream = $this->openStream();

        \flock($stream, \LOCK_EX);

        return $stream;
    }

    /**
     * @param resource $stream
     */
    private function closeStream(mixed $stream): void
    {
        if (!\is_resource($stream)) {
            return;
        }

        \fclose($stream);
    }

    /**
     * @param resource $stream
     */
    private function closeAndUnlockStream(mixed $stream): void
    {
        if (!\is_resource($stream)) {
            return;
        }

        \flock($stream, \LOCK_UN);

        $this->closeStream($stream);
    }

    public function __invoke(Configuration $config): void
    {
        Task::info('Download %s', [
            $this->sourceUri,
        ]);

        if (\is_readable($this->target)) {
            Task::notify('File already downloaded');

            return;
        }

        $stream = $this->openAndLockStream();

        try {
            Task::notify('Establish connection');

            $external = $this->client->stream($this->client->request('GET', $this->sourceUri));

            Task::notify('Ready to download');

            foreach ($external as $chunk) {
                \fwrite($stream, $chunk->getContent());

                Task::progress(\number_format($chunk->getOffset() / 1e3, 2) . 'KiB');
            }
        } catch (\Throwable $e) {
            $this->closeAndUnlockStream($stream);

            Task::run($config, new DeleteFileTask($this->target));

            throw $e;
        }

        Task::notify('Downloading complete', [
            $this->sourceUri,
        ]);

        $this->closeAndUnlockStream($stream);
    }
}
