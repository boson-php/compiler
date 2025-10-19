<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Task;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Configuration\DirectoryIncludeConfiguration;
use Boson\Component\Compiler\Configuration\FileIncludeConfiguration;
use Boson\Component\Compiler\Configuration\FinderIncludeConfiguration;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\Support\Path;

final readonly class CreateBoxConfigTask implements TaskInterface
{
    public const int DEFAULT_JSON_BUILD_FLAGS = \JSON_PRETTY_PRINT;

    public function __construct(
        private int $jsonFlags = self::DEFAULT_JSON_BUILD_FLAGS,
        private bool $enableCompression = true,
    ) {}

    public function __invoke(Configuration $config): void
    {
        Task::info('Build "%s" humbug/box configuration file', [
            Path::simplify($config, $config->boxConfigPathname),
        ]);

        // Update box.json config in case of configuration
        // file is more relevant than generated config file.
        if (!$this->shouldUpdateOrCreateBoxConfig($config)) {
            Task::notify('Nothing to update, configuration file is actual');

            return;
        }

        Task::run($config, new CreateFileTask(
            pathname: $config->boxConfigPathname,
            content: \json_encode(
                value: $this->getBoxConfig($config),
                flags: $this->jsonFlags | \JSON_THROW_ON_ERROR,
            ),
            overwrite: true,
        ));

        Task::notify('The humbug/box configuration file has been created');
    }

    /**
     * @return list<array{
     *     in: non-empty-list<non-empty-string>,
     *     name?: list<non-empty-string>,
     *     exclude?: list<non-empty-string>,
     *     notName?: list<non-empty-string>,
     * }>
     */
    private function getBoxFinderConfig(Configuration $config): array
    {
        $finder = [];

        foreach ($config->build as $inclusion) {
            if (!$inclusion instanceof FinderIncludeConfiguration) {
                continue;
            }

            if ($inclusion->directories === []) {
                throw new \InvalidArgumentException(\sprintf(
                    'Directories list cannot be empty in "build.finder[%s].directories" config section',
                    \count($finder),
                ));
            }

            $section = ['in' => $inclusion->directories];

            if ($inclusion->notDirectories !== []) {
                $section['exclude'] = $inclusion->notDirectories;
            }

            if ($inclusion->names !== []) {
                $section['name'] = $inclusion->names;
            }

            if ($inclusion->notNames !== []) {
                $section['notName'] = $inclusion->notNames;
            }

            $finder[] = $section;
        }

        return $finder;
    }

    /**
     * @return list<non-empty-string>
     */
    private function getBoxFilesConfig(Configuration $config): array
    {
        $files = [];

        foreach ($config->build as $inclusion) {
            if (!$inclusion instanceof FileIncludeConfiguration) {
                continue;
            }

            $files[] = $inclusion->pathname;
        }

        return $files;
    }

    /**
     * @return list<non-empty-string>
     */
    private function getBoxDirectoriesConfig(Configuration $config): array
    {
        $directories = [];

        foreach ($config->build as $inclusion) {
            if (!$inclusion instanceof DirectoryIncludeConfiguration) {
                continue;
            }

            $directories[] = $inclusion->directory;
        }

        return $directories;
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    private function getBoxConfig(Configuration $config): array
    {
        $finder = $this->getBoxFinderConfig($config);
        $directories = $this->getBoxDirectoriesConfig($config);
        $files = $this->getBoxFilesConfig($config);
        $files[] = $config->entrypoint;

        return [
            'base-path' => $config->root,
            'check-requirements' => false,
            'dump-autoload' => $this->getBoxConfigDumpAutoloadOption($config),
            'stub' => $config->entrypointPathname,
            'output' => $config->pharPathname,
            'exclude-composer-files' => $this->getBoxConfigComposerExclusionOption($config),
            'main' => false,
            'chmod' => '0644',
            'compression' => $this->getBoxConfigCompression($config),
            'finder' => $finder,
            'files' => $files,
            'directories' => $directories,
        ];
    }

    private function getBoxConfigDumpAutoloadOption(Configuration $config): bool
    {
        return false;
    }

    private function getBoxConfigComposerExclusionOption(Configuration $config): bool
    {
        return true;
    }

    private function getBoxConfigCompression(Configuration $config): string
    {
        if ($this->enableCompression) {
            return 'GZ';
        }

        return 'NONE';
    }

    private function shouldUpdateOrCreateBoxConfig(Configuration $config): bool
    {
        return $this->getBoxConfigTimestamp($config) < $config->timestamp;
    }

    private function getBoxConfigTimestamp(Configuration $config): int
    {
        if (\is_file($config->boxConfigPathname) && ($time = \filemtime($config->boxConfigPathname)) !== false) {
            return $time;
        }

        return \PHP_INT_MIN;
    }
}
