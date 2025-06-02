<?php

declare(strict_types=1);

namespace Boson\Component\Compiler;

use Boson\Component\Compiler\Assembly\Assembly;
use Boson\Component\Compiler\Configuration\FinderIncludeConfiguration;
use Boson\Component\Compiler\Configuration\IncludeConfiguration;

final class Configuration
{
    /**
     * @var non-empty-string
     */
    public const string DEFAULT_APP_NAME = 'app';

    /**
     * @var non-empty-string
     */
    public const string DEFAULT_ENTRYPOINT = 'index.php';
    /**
     * @var non-empty-string
     */
    public const string DEFAULT_BOX_VERSION = '4.6.6';

    /**
     * @var non-empty-string|null
     */
    public const ?string DEFAULT_BUILD_DIRECTORY = null;

    /**
     * @var non-empty-string|null
     */
    public const ?string DEFAULT_APP_DIRECTORY = null;

    /**
     * @var list<FinderIncludeConfiguration>
     */
    public private(set) array $buildFiles;

    /**
     * @var list<FinderIncludeConfiguration>
     */
    public private(set) array $copyFiles;

    /**
     * @var array<non-empty-string, scalar>
     */
    public private(set) array $ini;

    /**
     * @var non-empty-string
     */
    public private(set) string $build {
        get => $this->build;
        set(?string $directory) => $directory
            ?? ($this->root . \DIRECTORY_SEPARATOR . 'build');
    }

    /**
     * @var non-empty-string
     */
    public private(set) string $root {
        get => $this->root ??= (\getcwd() ?: '.');
        set(?string $directory) => $directory ?? (\getcwd() ?: '.');
    }

    /**
     * @var non-empty-string
     */
    public string $pharName {
        get => $this->name . '.phar';
    }

    /**
     * @var non-empty-string
     */
    public string $pharPathname {
        get => $this->build . \DIRECTORY_SEPARATOR . $this->pharName;
    }

    /**
     * @var non-empty-string
     */
    public string $boxStubName {
        get => 'entrypoint.php';
    }

    /**
     * @var non-empty-string
     */
    public string $boxStubPathname {
        get => $this->build . \DIRECTORY_SEPARATOR . $this->boxStubName;
    }

    /**
     * @var non-empty-string
     */
    public string $boxConfigName {
        get => 'box.json';
    }

    /**
     * @var non-empty-string
     */
    public string $boxConfigPathname {
        get => $this->build . \DIRECTORY_SEPARATOR . $this->boxConfigName;
    }

    /**
     * @var non-empty-string
     */
    public string $boxPharName {
        get => 'box-' . $this->boxVersion . '.phar';
    }

    /**
     * @var non-empty-string
     */
    public string $boxPharPathname {
        get => $this->build . \DIRECTORY_SEPARATOR . $this->boxPharName;
    }

    /**
     * @var non-empty-string
     */
    public string $boxUri {
        get => \vsprintf('https://github.com/box-project/box/releases/download/%s/box.phar', [
            $this->boxVersion,
        ]);
    }

    /**
     * @param iterable<mixed, IncludeConfiguration> $build
     * @param iterable<mixed, FinderIncludeConfiguration> $copy
     * @param iterable<non-empty-string, scalar> $ini
     * @param non-empty-string|null $temp
     * @param non-empty-string|null $root
     */
    public function __construct(
        /**
         * @var non-empty-string
         */
        public private(set) string $name = self::DEFAULT_APP_NAME,
        /**
         * @var non-empty-string
         */
        public private(set) string $entrypoint = self::DEFAULT_ENTRYPOINT,
        /**
         * @var non-empty-string
         */
        public private(set) string $boxVersion = self::DEFAULT_BOX_VERSION,
        ?string $temp = self::DEFAULT_BUILD_DIRECTORY,
        ?string $root = self::DEFAULT_APP_DIRECTORY,
        iterable $build = [],
        iterable $copy = [],
        iterable $ini = [],
    ) {
        $this->buildFiles = \iterator_to_array($build, false);
        $this->copyFiles = \iterator_to_array($copy, false);
        $this->ini = \iterator_to_array($ini, false);
        $this->build = $temp;
        $this->root = $root;
    }

    public static function createDefaultConfiguration(): self
    {
        return new self();
    }

    /**
     * @param non-empty-string $name
     */
    public function withName(string $name): self
    {
        $self = clone $this;
        $self->name = $name;

        return $self;
    }

    /**
     * @param non-empty-string $entrypoint
     */
    public function withEntrypoint(string $entrypoint): self
    {
        $self = clone $this;
        $self->entrypoint = $entrypoint;

        return $self;
    }

    /**
     * @param non-empty-string $version
     */
    public function withBoxVersion(string $version): self
    {
        $self = clone $this;
        $self->boxVersion = $version;

        return $self;
    }

    /**
     * @param non-empty-string|null $directory
     */
    public function withBuildDirectory(?string $directory): self
    {
        $self = clone $this;
        $self->build = $directory;

        return $self;
    }

    /**
     * @param non-empty-string|null $directory
     */
    public function withRootDirectory(?string $directory): self
    {
        $self = clone $this;
        $self->root = $directory;

        return $self;
    }

    /**
     * @param non-empty-string $config
     * @param string|float|bool|int $value
     */
    public function withAddedIni(string $config, string|float|bool|int $value): self
    {
        $self = clone $this;
        $self->ini[$config] = $value;

        return $self;
    }

    public function withAddedBuildInclusion(IncludeConfiguration $config): self
    {
        $self = clone $this;
        $self->buildFiles[] = $config;

        return $self;
    }

    public function withAddedCopyInclusion(IncludeConfiguration $config): self
    {
        $self = clone $this;
        $self->copyFiles[] = $config;

        return $self;
    }
}
