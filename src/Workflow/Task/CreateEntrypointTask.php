<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Workflow\Task;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\Support\Path;

final readonly class CreateEntrypointTask implements TaskInterface
{
    /**
     * @var non-empty-string
     */
    private const string LOCAL_STUB_PATHNAME = __DIR__ . '/../../../resources/stub.php';

    public function __invoke(Configuration $config): void
    {
        Task::info('Build "%s" entrypoint file', [
            Path::simplify($config, $config->entrypointPathname),
        ]);

        // Update box stub file in case of configuration
        // file is more relevant than generated config file.
        if (!$this->shouldUpdateOrCreateBoxStub($config)) {
            Task::notify('Nothing to update, entrypoint file is actual');

            return;
        }

        Task::run($config, new CreateFileTask(
            pathname: $config->entrypointPathname,
            content: $this->getStub($config),
            overwrite: true,
        ));

        Task::notify('Entrypoint file has been created');
    }

    private function getStub(Configuration $config): string
    {
        $stub = Task::run($config, new ReadFileTask(
            pathname: self::LOCAL_STUB_PATHNAME,
        ));

        return $this->applyVariables($config, $stub);
    }

    private function applyVariables(Configuration $config, string $content): string
    {
        return \str_replace(
            search: ['{name}', '{entrypoint}', '{mount}'],
            replace: [$config->name, $config->entrypoint, \var_export($config->mount, true)],
            subject: $content,
        );
    }

    private function shouldUpdateOrCreateBoxStub(Configuration $config): bool
    {
        return $this->getBoxStubTimestamp($config) < $config->timestamp;
    }

    private function getBoxStubTimestamp(Configuration $config): int
    {
        if (\is_file($config->entrypointPathname) && ($time = \filemtime($config->entrypointPathname)) !== false) {
            return $time;
        }

        return \PHP_INT_MIN;
    }
}
