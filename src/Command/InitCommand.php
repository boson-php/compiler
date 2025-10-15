<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Command;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

final class InitCommand extends ConfigAwareCommand
{
    /**
     * @var non-empty-string
     */
    private const string BOSON_JSON_TEMPLATE = __DIR__ . '/../../resources/boson.json';

    public function __construct(?string $name = null)
    {
        parent::__construct($name ?? 'init');
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Initialize Boson configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->getConfiguration($input);

        $boson = $config->root . '/boson.json';

        if (\is_file($boson)) {
            $output->writeln(\sprintf('The config "<comment>%s</comment>" already exists', $boson));

            $helper = $this->getHelper('question');

            if (!$helper instanceof QuestionHelper) {
                throw new \InvalidArgumentException('Could not initialize question helper');
            }

            $shouldContinue = $helper->ask($input, $output, new ConfirmationQuestion(
                question: ' · Do you want to overwrite it? [y/N] ',
                default: false,
                trueAnswerRegex: '/^(y|j)/i'
            ));

            if ($shouldContinue === false) {
                return self::SUCCESS;
            }
        }

        \file_put_contents(
            filename: $boson,
            data: \json_encode(\file_get_contents(self::BOSON_JSON_TEMPLATE)),
            flags: \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT,
        );

        $output->writeln(\sprintf(
            ' <info>●</info> Configuration "<comment>%s</comment>" was successfully created',
            $boson,
        ));

        return self::SUCCESS;
    }
}
