<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinArchitectureTarget;
use Boson\Component\Compiler\Target\Factory\BuiltinTargetFactory\BuiltinPlatformTarget;

abstract readonly class BuiltinTarget extends Target
{
    /**
     * @param non-empty-string $type
     * @param non-empty-string|null $output
     * @param array<array-key, mixed> $config
     */
    public function __construct(
        public BuiltinArchitectureTarget $arch,
        string $type,
        ?string $output,
        array $config,
    ) {
        parent::__construct(
            type: $type,
            output: $output ?? $type . '/' . $arch->value,
            config: $config,
        );
    }

    protected function process(Configuration $config): iterable
    {
        yield $output = $this->getAndValidateBuildDirectory($config);
        yield $target = $this->getTargetBinary($output, $config);
        yield from $this->build($target, $config);
        yield from $this->copyRuntimeBinary($output);
    }

    /**
     * @return non-empty-string
     */
    abstract protected function getRuntimeBinaryFilename(): string;

    /**
     * @return iterable<array-key, mixed>
     */
    protected function copyRuntimeBinary(string $output): iterable
    {
        $runtimeBinary = $this->getRuntimeBinaryFilename();

        $runtimeSourcePathname = $this->getSourceRuntimeBinDirectory()
            . \DIRECTORY_SEPARATOR
            . $runtimeBinary;

        $runtimeTargetPathname = $output
            . \DIRECTORY_SEPARATOR
            . $runtimeBinary;

        yield $this->copyOrFail($runtimeSourcePathname, $runtimeTargetPathname);
    }

    protected function unsupportedArchitectureOfPlatform(
        BuiltinPlatformTarget $platform,
        BuiltinArchitectureTarget $arch,
    ): \Throwable {
        return new \InvalidArgumentException(\sprintf(
            'The %s compilation target does not support "%s" architecture',
            $platform->value,
            $arch->value,
        ));
    }

    /**
     * @param non-empty-string $output
     *
     * @return non-empty-string
     */
    abstract protected function getTargetBinary(string $output, Configuration $config): string;

    /**
     * @param non-empty-string $target
     *
     * @return iterable<array-key, mixed>
     */
    protected function build(string $target, Configuration $config): iterable
    {
        yield $targetStream = @\fopen($target, 'wb+');

        if ($targetStream === false) {
            throw new \RuntimeException(\sprintf(
                'Unable to create target binary "%s"',
                $target,
            ));
        }

        yield \flock($targetStream, \LOCK_EX);

        yield from $this->appendSfxArchive($targetStream, $config);
        yield from $this->appendPhpConfig($targetStream, $config);
        yield from $this->appendSource($targetStream, $config);

        yield \flock($targetStream, \LOCK_UN);
        yield \fclose($targetStream);
    }

    /**
     * @return non-empty-string
     */
    private function getPhpConfigString(Configuration $config): string
    {
        $ini = <<<'INI'
            ffi.enable=1
            opcache.enable=1
            INI;

        foreach ($config->ini as $key => $value) {
            $ini .= "\n$key=" . match ($value) {
                false => '0',
                true => '1',
                default => (string) $value,
            };
        }

        return $ini . "\n";
    }

    abstract protected function getSfxArchivePathname(Configuration $config): string;

    /**
     * @param resource $stream
     *
     * @return iterable<array-key, mixed>
     */
    private function appendSource(mixed $stream, Configuration $config): iterable
    {
        yield $sourceStream = @\fopen($config->pharPathname, 'rb');

        if ($sourceStream === false) {
            throw new \RuntimeException(\sprintf(
                'Unable to open application phar file "%s"',
                $config->pharPathname,
            ));
        }

        yield \flock($sourceStream, \LOCK_SH);
        yield \stream_copy_to_stream($sourceStream, $stream);
        yield \fclose($sourceStream);
    }

    /**
     * @param resource $stream
     *
     * @return iterable<array-key, int|false>
     */
    private function appendPhpConfig(mixed $stream, Configuration $config): iterable
    {
        yield $ini = $this->getPhpConfigString($config);

        yield \fwrite($stream, "\xfd\xf6\x69\xe6");
        yield \fwrite($stream, \pack('N', \strlen($ini)));
        yield \fwrite($stream, $ini);
    }

    /**
     * @param resource $stream
     *
     * @return iterable<array-key, mixed>
     */
    private function appendSfxArchive(mixed $stream, Configuration $config): iterable
    {
        yield $archive = $this->getSfxArchivePathname($config);
        yield $archiveStream = @\fopen($archive, 'rb');

        if ($archiveStream === false) {
            throw new \RuntimeException(\sprintf(
                'Unable to open application SFX file "%s"',
                $archive,
            ));
        }

        yield \flock($archiveStream, \LOCK_SH);
        yield \stream_copy_to_stream($archiveStream, $stream);
        yield \fclose($archiveStream);
    }

    public function __toString(): string
    {
        return $this->output;
    }
}
