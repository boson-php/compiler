<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Configuration\Factory\JsonConfiguration;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Configuration\Factory\JsonConfiguration\JsonConfigLoader\DecodedJsonConfigInfo;
use Boson\Component\Compiler\Configuration\Factory\JsonConfiguration\JsonConfigLoader\LoadedJsonConfigInfo;

final readonly class JsonConfigLoader
{
    /**
     * @var int<1, max>
     */
    public const int DEFAULT_DECODE_RECURSION_DEPTH = 64;

    /**
     * @var non-empty-string
     */
    public const string DEFAULT_JSON_FILENAME = 'boson.json';

    public function __construct(
        /**
         * @var int<1, max>
         */
        private int $decodeRecursionDepth = self::DEFAULT_DECODE_RECURSION_DEPTH,
    ) {}

    /**
     * @param non-empty-string $pathname
     */
    private function readConfigAsJsonStringFromReadable(string $pathname): ?string
    {
        $contents = @\file_get_contents($pathname);

        if ($contents === false) {
            return null;
        }

        return $contents;
    }

    /**
     * @param non-empty-string $filename
     */
    private function readConfigAsJsonString(string $filename, Configuration $config): ?LoadedJsonConfigInfo
    {
        $pathname = $this->getConfigPathname($filename, $config);

        if ($pathname === null) {
            return null;
        }

        $json = $this->readConfigAsJsonStringFromReadable($pathname);

        if ($json === null) {
            return null;
        }

        return new LoadedJsonConfigInfo(
            pathname: $pathname,
            json: $json,
        );
    }

    /**
     * @param non-empty-string $filename
     *
     * @return non-empty-string|null
     */
    private function getConfigPathname(string $filename, Configuration $config): ?string
    {
        if (\is_readable($filename)) {
            return $filename;
        }

        if (\is_readable($pathname = $config->root . '/' . $filename)) {
            return $pathname;
        }

        return null;
    }

    /**
     * @param non-empty-string $filename
     *
     * @throws \JsonException
     */
    private function readConfigAsObject(string $filename, Configuration $config): ?DecodedJsonConfigInfo
    {
        $loaded = $this->readConfigAsJsonString($filename, $config);

        if ($loaded === null) {
            return null;
        }

        return new DecodedJsonConfigInfo(
            pathname: $loaded->pathname,
            data: (array) \json_decode(
                json: $loaded->json,
                associative: true,
                depth: $this->decodeRecursionDepth,
                flags: \JSON_THROW_ON_ERROR,
            ),
        );
    }

    /**
     * @param non-empty-string $filename
     */
    public function loadConfigOrFail(string $filename, Configuration $config): ?DecodedJsonConfigInfo
    {
        try {
            return $this->readConfigAsObject($filename, $config);
        } catch (\Throwable $e) {
            throw new \RuntimeException(\sprintf(
                '%s: An error occurred while parsing "%s" configuration file',
                $e->getMessage(),
                /** @phpstan-ignore ternary.shortNotAllowed */
                \realpath($filename) ?: $filename,
            ));
        }
    }
}
