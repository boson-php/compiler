<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Configuration\Factory\JsonConfiguration;

use Boson\Component\Compiler\Configuration\Factory\JsonConfigurationFactory;
use JsonSchema\Constraints\Constraint as JsonSchemaConstraint;
use JsonSchema\Validator;

/**
 * @phpstan-type JsonSchemaErrorType array<array-key, array{
 *     message: non-empty-string,
 *     property: non-empty-string,
 *     constraint?: array{
 *         name?: string,
 *         ...
 *     },
 *     ...
 * }>
 *
 * @phpstan-import-type RawConfigurationType from JsonConfigurationFactory
 */
final readonly class JsonConfigValidator
{
    /**
     * @var non-empty-string
     */
    private const string JSON_SCHEMA_PATHNAME = __DIR__ . '/../../../../resources/boson.schema.json';

    public function __construct(
        /**
         * @var non-empty-string
         */
        private string $jsonSchemaPathname = self::JSON_SCHEMA_PATHNAME,
    ) {}

    /**
     * @phpstan-assert RawConfigurationType $data
     *
     * @param non-empty-string $pathname
     */
    public function validateOrFail(array $data, string $pathname): void
    {
        $validator = $this->validate($data);

        $message = $this->getFormattedErrorMessage($validator->getErrors());

        if ($message === null) {
            return;
        }

        throw new \InvalidArgumentException(\sprintf(
            "The following configuration errors were found in \"%s\": \n%s",
            $pathname,
            $message,
        ));
    }

    private function validate(array $data): Validator
    {
        $validator = new Validator();

        $validator->validate($data, (object) [
            '$ref' => $this->jsonSchemaPathname,
        ], JsonSchemaConstraint::CHECK_MODE_TYPE_CAST);

        return $validator;
    }

    /**
     * @param JsonSchemaErrorType $errors
     *
     * @return non-empty-string|null
     */
    private function getFormattedErrorMessage(array $errors): ?string
    {
        $result = [];

        foreach ($this->filterErrorMessages($errors) as $localPath => $localMessages) {
            $realPath = \rtrim($localPath, '.');

            $result[] = \vsprintf("- An error at \"%s\":\n  └ %s", [
                $realPath,
                \implode("\n  └ ", $localMessages),
            ]);
        }

        if ($result === []) {
            return null;
        }

        return \implode("\n", $result);
    }

    /**
     * @param JsonSchemaErrorType $errors
     *
     * @return iterable<non-empty-string, list<non-empty-string>>
     */
    private function filterErrorMessages(array $errors): iterable
    {
        $processedPaths = [];

        foreach ($this->groupErrorMessages($errors) as $path => $messages) {
            if ($this->containsInPath($processedPaths, $path)) {
                continue;
            }

            // @phpstan-ignore-next-line : PHPStan generator false-positive
            yield $path => $messages;

            $processedPaths[] = $path;
        }
    }

    /**
     * @param list<non-empty-string> $processedPaths
     * @param non-empty-string $path
     */
    private function containsInPath(array $processedPaths, string $path): bool
    {
        foreach ($processedPaths as $processedPath) {
            if (\str_contains($processedPath, $path . '.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param JsonSchemaErrorType $errors
     *
     * @return array<non-empty-string, list<non-empty-string>>
     */
    private function groupErrorMessages(array $errors): array
    {
        $groups = [];

        foreach ($errors as $error) {
            switch ($error['constraint']['name'] ?? null) {
                // Exclude "anyOf" constraints
                case 'anyOf':
                    continue 2;
                default:
                    $groups[$error['property']][] = $error['message'];
            }
        }

        return $groups;
    }
}
