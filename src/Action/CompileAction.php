<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Target\TargetInterface;

/**
 * @template-extends TargetAction<CompileStatus>
 */
final readonly class CompileAction extends TargetAction
{
    /**
     * @var non-empty-string
     */
    private const string DEFAULT_INI_CONFIG = <<<'INI'
                             ffi.enable=1
                             INI;

    public function __construct(
        /**
         * @var non-empty-string
         */
        private string $sfx,
        /**
         * @var non-empty-string
         */
        private string $targetFilename,
        TargetInterface $target,
    ) {
        parent::__construct($target);
    }

    public function process(Configuration $config): iterable
    {
        yield $this->target => CompileStatus::ReadyToCompile;

        $targetPathname = $this->getBuildDirectory($config)
            . \DIRECTORY_SEPARATOR
            . $this->targetFilename;

        $targetStream = @\fopen($targetPathname, 'wb+');

        if ($targetStream === false) {
            throw new \RuntimeException(\sprintf(
                'Unable to create target binary "%s"',
                $targetPathname,
            ));
        }

        \flock($targetStream, \LOCK_EX);

        yield $this->target => CompileStatus::Progress;
        $this->appendSfxArchive($targetStream);

        yield $this->target => CompileStatus::Progress;
        yield $this->appendPhpConfig($targetStream, $config) => CompileStatus::BuildConfiguration;

        yield $this->target => CompileStatus::Progress;
        $this->appendSource($targetStream, $config);

        \flock($targetStream, \LOCK_UN);
        \fclose($targetStream);

        yield $this->target => CompileStatus::Compiled;
    }

    /**
     * @return non-empty-string
     */
    private function getPhpConfigString(Configuration $config): string
    {
        $ini = self::DEFAULT_INI_CONFIG;

        /**
         * @var non-empty-string $key
         * @var scalar $value
         * @phpstan-ignore-next-line : Additional target's ini configuration is valid array (checked by json schema)
         */
        foreach ([...$config->ini, ...$this->target->config['ini'] ?? []] as $key => $value) {
            $ini .= "\n$key=" . match ($value) {
                false => '0',
                true => '1',
                default => (string) $value,
            };
        }

        return $ini . "\n";
    }

    /**
     * @param resource $stream
     */
    private function appendSource(mixed $stream, Configuration $config): void
    {
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
    }

    /**
     * @param resource $stream
     */
    private function appendPhpConfig(mixed $stream, Configuration $config): string
    {
        $ini = $this->getPhpConfigString($config);

        \fwrite($stream, "\xfd\xf6\x69\xe6");
        \fwrite($stream, \pack('N', \strlen($ini)));
        \fwrite($stream, $ini);

        return $ini;
    }

    /**
     * @param resource $stream
     */
    private function appendSfxArchive(mixed $stream): void
    {
        $archiveStream = @\fopen($this->sfx, 'rb');

        if ($archiveStream === false) {
            throw new \RuntimeException(\sprintf(
                'Unable to open application SFX file "%s"',
                $this->sfx,
            ));
        }

        \flock($archiveStream, \LOCK_SH);
        \stream_copy_to_stream($archiveStream, $stream);
        \fclose($archiveStream);
    }
}
