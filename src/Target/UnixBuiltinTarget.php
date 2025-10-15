<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Target;

use Boson\Component\Compiler\Action\ApplyExecutePermissionsAction;
use Boson\Component\Compiler\Configuration;

abstract readonly class UnixBuiltinTarget extends BuiltinTarget
{
    protected function getTargetFilename(Configuration $config): string
    {
        return $config->name;
    }

    #[\Override]
    protected function process(Configuration $config): iterable
    {
        yield from parent::process($config);

        yield from new ApplyExecutePermissionsAction(
            targetFilename: $this->getTargetFilename($config),
            target: $this
        )
            ->process($config);
    }

    /**
     * @param list<non-empty-string> $actual
     * @param non-empty-string $platform
     */
    protected function missingExtensionsError(Configuration $config, array $actual, string $platform): \Throwable
    {
        $missing = \implode(', ', $this->getMissingDependencies(
            config: $config,
            actual: $actual,
        ));

        $expected = \implode(', ', $this->getExpectedDependencies($config));

        return new \RuntimeException(\sprintf(
            <<<'MESSAGE'
                An expected [%s] extensions not supported by this compile target, please add it manually:
                1) Fork this repository: https://github.com/boson-php/backend-src
                2) Open GitHub Actions: https://github.com/USERNAME/backend-src/actions/workflows/build-unix.yml
                3) Press "Run workflow" dropdown
                3.1) Select "%s" in "Build target OS"
                3.2) Disable "build cli binary" checkbox
                3.3) Enable "build phpmicro binary" checkbox
                3.4) Insert "%s" into "extensions to build" text input
                4) Press "Run workflow" dropdown button
                5) Download compiled SFX assembly
                6) Add SFX assembly to "sfx" configuration section of this compile target
                MESSAGE,
            $missing,
            $platform,
            $expected,
        ));
    }
}
