<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Configuration\Factory;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Configuration\DirectoryIncludeConfiguration;
use Boson\Component\Compiler\Configuration\Factory\JsonConfiguration\JsonConfigLoader;
use Boson\Component\Compiler\Configuration\Factory\JsonConfiguration\JsonConfigTargetFactory;
use Boson\Component\Compiler\Configuration\Factory\JsonConfiguration\JsonConfigValidator;
use Boson\Component\Compiler\Configuration\FileIncludeConfiguration;
use Boson\Component\Compiler\Configuration\FinderIncludeConfiguration;
use Boson\Component\Compiler\Configuration\IncludeConfiguration;
use Boson\Component\Compiler\Target\TargetFactoryInterface;

/**
 * @phpstan-import-type CompilationTargetConfigType from TargetFactoryInterface
 *
 * @phpstan-type OneOrManyReferencesType non-empty-string|non-empty-list<non-empty-string>
 * @phpstan-type RawFinderInclusionType array{
 *     directory: OneOrManyReferencesType,
 *     not-directory?: OneOrManyReferencesType,
 *     name?: OneOrManyReferencesType,
 *     not-name?: OneOrManyReferencesType
 * }
 * @phpstan-type RawFileInclusionType non-empty-string
 * @phpstan-type RawDirectoryInclusionType non-empty-string
 * @phpstan-type RawBuildConfigurationType array{
 *     files: list<RawFileInclusionType>,
 *     directories: list<RawDirectoryInclusionType>,
 *     finder: list<RawFinderInclusionType>
 * }
 * @phpstan-type RawConfigurationType array{
 *     name?: non-empty-string,
 *     target: non-empty-list<CompilationTargetConfigType>,
 *     entrypoint?: non-empty-string,
 *     output?: non-empty-string,
 *     root?: non-empty-string,
 *     mount?: list<non-empty-string>,
 *     extensions?: list<non-empty-string>,
 *     build?: RawBuildConfigurationType,
 *     ini?: array<non-empty-string, string|int|float|bool>,
 *     box-version?: non-empty-string,
 * }
 */
final readonly class JsonConfigurationFactory implements ConfigurationFactoryInterface
{
    /**
     * @var non-empty-string
     */
    public const string DEFAULT_JSON_FILENAME = JsonConfigLoader::DEFAULT_JSON_FILENAME;

    private JsonConfigTargetFactory $targets;

    /**
     * JSON configuration loader
     */
    private JsonConfigLoader $loader;

    /**
     * JSON configuration validator
     */
    private JsonConfigValidator $validator;

    public function __construct(
        /**
         * @var non-empty-string
         */
        private string $filename = JsonConfigLoader::DEFAULT_JSON_FILENAME,
    ) {
        $this->targets = new JsonConfigTargetFactory();
        $this->validator = new JsonConfigValidator();
        $this->loader = new JsonConfigLoader();
    }

    public function createConfiguration(Configuration $config): Configuration
    {
        $loaded = $this->loader->loadConfigOrFail($this->filename, $config);

        if ($loaded === null) {
            return $config;
        }

        [$pathname, $input] = [$loaded->pathname, $loaded->data];

        $this->validator->validateOrFail($input, $pathname);

        $config = $this->extendTimestamp($config, $pathname);
        $config = $this->extendName($input, $config);
        $config = $this->extendEntrypoint($input, $config);
        $config = $this->extendBoxVersion($input, $config);
        $config = $this->extendOutputDirectory($input, $config);
        $config = $this->extendRootDirectory($input, $config);
        $config = $this->extendBuildConfiguration($input, $config);
        $config = $this->extendIni($input, $config);
        $config = $this->extendMount($input, $config);
        $config = $this->extendExtension($input, $config);
        $config = $this->extendCompilationTargets($input, $config);

        return $config;
    }

    /**
     * @param RawConfigurationType $input
     */
    private function extendCompilationTargets(array $input, Configuration $config): Configuration
    {
        foreach ($input['target'] as $inputTarget) {
            $config = $config->withAddedTarget(
                target: $this->targets->create($inputTarget, $config),
            );
        }

        return $config;
    }

    /**
     * @param non-empty-string $pathname
     */
    private function extendTimestamp(Configuration $config, string $pathname): Configuration
    {
        $time = @\filemtime($pathname);

        if ($time === false) {
            return $config;
        }

        return $config->withTimestamp($time);
    }

    /**
     * @param RawConfigurationType $input
     */
    private function extendName(array $input, Configuration $config): Configuration
    {
        if (!isset($input['name'])) {
            return $config;
        }

        return $config->withName($input['name']);
    }

    /**
     * @param RawConfigurationType $input
     */
    private function extendEntrypoint(array $input, Configuration $config): Configuration
    {
        if (!isset($input['entrypoint'])) {
            return $config;
        }

        return $config->withEntrypoint($input['entrypoint']);
    }

    /**
     * @param RawConfigurationType $input
     */
    private function extendBoxVersion(array $input, Configuration $config): Configuration
    {
        if (!isset($input['box-version'])) {
            return $config;
        }

        return $config->withBoxVersion($input['box-version']);
    }

    /**
     * @param RawConfigurationType $input
     */
    private function extendOutputDirectory(array $input, Configuration $config): Configuration
    {
        if (!isset($input['output'])) {
            return $config;
        }

        return $config->withOutputDirectory($input['output']);
    }

    /**
     * @param RawConfigurationType $input
     */
    private function extendRootDirectory(array $input, Configuration $config): Configuration
    {
        if (isset($input['root'])) {
            $root = $input['root'];

            if (\is_dir($root)) {
                /** @var non-empty-string $root */
                $root = (string) @\realpath($root);
            }

            return $config->withRootDirectory($root);
        }

        /** @var non-empty-string $root */
        $root = \dirname((string) \realpath($this->filename));

        return $config->withRootDirectory($root);
    }

    /**
     * @param RawConfigurationType $input
     */
    private function extendBuildConfiguration(array $input, Configuration $config): Configuration
    {
        if (!isset($input['build'])) {
            return $config;
        }

        $config = $this->extendBuildFilesConfiguration($input['build'], $config);
        $config = $this->extendBuildDirectoriesConfiguration($input['build'], $config);
        $config = $this->extendBuildFinderConfiguration($input['build'], $config);

        return $config;
    }

    /**
     * @param RawBuildConfigurationType $input
     */
    private function extendBuildFilesConfiguration(array $input, Configuration $config): Configuration
    {
        if (!isset($input['files'])) {
            return $config;
        }

        foreach ($input['files'] as $fileInclusion) {
            $config = $config->withAddedBuildInclusion(
                config: $this->createFileInclusion($fileInclusion),
            );
        }

        return $config;
    }

    /**
     * @param RawBuildConfigurationType $input
     */
    private function extendBuildDirectoriesConfiguration(array $input, Configuration $config): Configuration
    {
        if (!isset($input['directories'])) {
            return $config;
        }

        foreach ($input['directories'] as $directoryInclusion) {
            $config = $config->withAddedBuildInclusion(
                config: $this->createDirectoryInclusion($directoryInclusion),
            );
        }

        return $config;
    }

    /**
     * @param RawBuildConfigurationType $input
     */
    private function extendBuildFinderConfiguration(array $input, Configuration $config): Configuration
    {
        if (!isset($input['finder'])) {
            return $config;
        }

        foreach ($input['finder'] as $finder) {
            $config = $config->withAddedBuildInclusion(
                config: $this->createFinderInclusion($finder),
            );
        }

        return $config;
    }

    /**
     * @param RawConfigurationType $input
     */
    private function extendIni(array $input, Configuration $config): Configuration
    {
        if (!isset($input['ini'])) {
            return $config;
        }

        foreach ((array) $input['ini'] as $iniConfig => $iniValue) {
            $config = $config->withAddedIni($iniConfig, $iniValue);
        }

        return $config;
    }

    /**
     * @param RawConfigurationType $input
     */
    private function extendExtension(array $input, Configuration $config): Configuration
    {
        if (!isset($input['extensions'])) {
            return $config;
        }

        foreach ((array) $input['extensions'] as $extension) {
            $config = $config->withAddedExtension($extension);
        }

        return $config;
    }

    /**
     * @param RawConfigurationType $input
     */
    private function extendMount(array $input, Configuration $config): Configuration
    {
        if (!isset($input['mount'])) {
            return $config;
        }

        foreach ((array) $input['mount'] as $mountDirectory) {
            $config = $config->withAddedMount($mountDirectory);
        }

        return $config;
    }

    /**
     * @param RawFinderInclusionType $inclusion
     */
    private function createFinderInclusion(array $inclusion): IncludeConfiguration
    {
        $directories = $inclusion['directory'];

        /**
         * @phpstan-var non-empty-string|list<non-empty-string> $notDirectories
         */
        $notDirectories = $inclusion['not-directory'] ?? [];

        $names = $inclusion['name'] ?? [];

        /**
         * @phpstan-var non-empty-string|list<non-empty-string> $notNames
         */
        $notNames = $inclusion['not-name'] ?? [];

        return new FinderIncludeConfiguration(
            directories: \is_string($directories) ? [$directories] : $directories,
            notDirectories: \is_string($notDirectories) ? [$notDirectories] : $notDirectories,
            names: \is_string($names) ? [$names] : $names,
            notNames: \is_string($notNames) ? [$notNames] : $notNames,
        );
    }

    /**
     * @param non-empty-string $inclusion
     */
    private function createFileInclusion(string $inclusion): FileIncludeConfiguration
    {
        return new FileIncludeConfiguration($inclusion);
    }

    /**
     * @param non-empty-string $inclusion
     */
    private function createDirectoryInclusion(string $inclusion): DirectoryIncludeConfiguration
    {
        return new DirectoryIncludeConfiguration($inclusion);
    }
}
