<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Command;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Configuration\Factory\ConsoleInputConfigurationFactory;
use Boson\Component\Compiler\Configuration\Factory\JsonConfigurationFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class ConfigAwareCommand extends Command
{
    private ?Configuration $config = null;

    protected function configure(): void
    {
        parent::configure();

        $this->initializeConfigOption();
        $this->initializeAppOption();
        $this->initializeEntrypointOption();
        $this->initializeBoxVersionOption();
        $this->initializeTempOption();
    }

    protected function initializeConfigOption(): void
    {
        $this->addOption(
            name: 'config',
            shortcut: 'c',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'Defines path to the configuration file',
            default: JsonConfigurationFactory::DEFAULT_JSON_FILENAME,
        );
    }

    protected function initializeAppOption(): void
    {
        $this->addOption(
            name: 'app',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'An application name',
        );
    }

    protected function initializeEntrypointOption(): void
    {
        $this->addOption(
            name: 'entry',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'An application entrypoint',
        );
    }

    protected function initializeBoxVersionOption(): void
    {
        $this->addOption(
            name: 'box-version',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'The "humbug/box" package version',
        );
    }

    protected function initializeTempOption(): void
    {
        $this->addOption(
            name: 'temp',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'The temp compilation directory',
        );
    }

    /**
     * Creates a new configuration instance
     */
    private function createDefaultConfiguration(): Configuration
    {
        return Configuration::createDefaultConfiguration();
    }

    /**
     * Return new configuration instance
     */
    private function getDefaultConfiguration(): Configuration
    {
        return $this->config ??= $this->createDefaultConfiguration();
    }

    /**
     * Returns configuration pathname using input "--config" option resolving
     */
    private function getConfigurationPathname(InputInterface $input): string
    {
        $pathname = $input->hasOption('config')
            ? $input->getOption('config')
            ?? JsonConfigurationFactory::DEFAULT_JSON_FILENAME
            : JsonConfigurationFactory::DEFAULT_JSON_FILENAME;

        if (!\is_string($pathname) || $pathname === '') {
            throw new \InvalidArgumentException('Could not read configuration file');
        }

        return $pathname;
    }

    /**
     * Returns new configuration with added JSON config file
     */
    private function getDefaultConfigurationWithAddedJson(InputInterface $input): Configuration
    {
        $pathname = $this->getConfigurationPathname($input);

        return new JsonConfigurationFactory($pathname)
            ->createConfiguration($this->getDefaultConfiguration());
    }

    /**
     * Returns new configuration with added input (stdin) config arguments
     */
    private function getDefaultConfigurationWithAddedInput(InputInterface $input): Configuration
    {
        $config = $this->getDefaultConfigurationWithAddedJson($input);

        return new ConsoleInputConfigurationFactory($input)
            ->createConfiguration($config);
    }

    /**
     * Returns new configuration
     */
    protected function getConfiguration(InputInterface $input): Configuration
    {
        return $this->getDefaultConfigurationWithAddedInput($input);
    }
}
