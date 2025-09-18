<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Action;

use Boson\Component\Compiler\Configuration;

/**
 * @template-implements ActionInterface<CreateBoxStubStatus>
 */
final readonly class CreateBoxStubAction implements ActionInterface
{
    private const string LOCAL_STUB_PATHNAME = __DIR__ . '/../../resources/stub.php';

    public function process(Configuration $config): iterable
    {
        yield CreateBoxStubStatus::ReadyToCreate;

        // Update stub in case of configuration file
        // is more relevant than stub.
        if ($this->getSharedStubTimestamp($config) < $config->timestamp) {
            $this->shareStubFile($config);
        }

        yield CreateBoxStubStatus::Created;
    }

    private function getSharedStubTimestamp(Configuration $config): int
    {
        if (\is_file($config->boxStubPathname) && ($time = \filemtime($config->boxStubPathname)) !== false) {
            return $time;
        }

        return \PHP_INT_MIN;
    }

    private function applyVariables(Configuration $config, string $content): string
    {
        return \str_replace(
            search: ['{name}', '{entrypoint}', '{mount}'],
            replace: [$config->name, $config->entrypoint, \var_export($config->mount, true)],
            subject: $content,
        );
    }

    private function shareStubFile(Configuration $config): void
    {
        $stub = (string) @\file_get_contents(self::LOCAL_STUB_PATHNAME);

        $stub = $this->applyVariables($config, $stub);

        \file_put_contents($config->boxStubPathname, $stub);
    }
}
