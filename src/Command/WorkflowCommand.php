<?php

declare(strict_types=1);

namespace Boson\Component\Compiler\Command;

use Boson\Component\Compiler\Configuration;
use Boson\Component\Compiler\Workflow\Step\InfoStep;
use Boson\Component\Compiler\Workflow\Step\MessageStep;
use Boson\Component\Compiler\Workflow\Step\NotifyStep;
use Boson\Component\Compiler\Workflow\Step\ProgressStep;
use Boson\Component\Compiler\Workflow\Step\Step;
use Boson\Component\Compiler\Workflow\Task;
use Boson\Component\Compiler\Workflow\Task\TaskInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class WorkflowCommand extends ConfigAwareCommand
{
    private const int DEFAULT_ROOT_DEPTH = 2;

    /**
     * @var array<non-empty-string, ProgressBar>
     */
    private array $progress = [];

    abstract protected function getWorkflow(InputInterface $input, Configuration $config): TaskInterface;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->getConfiguration($input);

        $process = Task::capture(function () use ($input, $config) {
            Task::run($config, $this->getWorkflow($input, $config));
        });

        $style = new SymfonyStyle($input, $output);

        try {
            foreach ($process as $step) {
                $this->render($style, $step);
            }
        } catch (\Throwable $e) {
            $this->completeProgressSteps();
            $this->fail($style, $e);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function render(SymfonyStyle $style, Step $step): void
    {
        $prefix = \str_repeat('  ', \max(0, $step->level - self::DEFAULT_ROOT_DEPTH));

        switch (true) {
            case $step instanceof ProgressStep:
                $this->writeProgressStep($prefix, $style, $step);
                break;

            case $step instanceof InfoStep:
                $this->completeProgressSteps();
                $this->writeInfoStep($prefix, $style, $step);
                break;

            case $step instanceof NotifyStep:
                $this->completeProgressSteps();
                $this->writeNotifyStep($prefix, $style, $step);
                break;

            case $step instanceof MessageStep:
                $this->completeProgressSteps();
                $this->writeMessageStep($prefix, $style, $step);
                break;
        }

        if ($step instanceof ProgressStep) {
            return;
        }

        $debug = $prefix . ' <fg=gray>  ↳ in ' . $step->context . '</>';

        $style->writeln($debug, OutputInterface::VERBOSITY_DEBUG);
    }

    private function completeProgressSteps(): void
    {
        foreach ($this->progress as $progress) {
            $progress->clear();
        }

        $this->progress = [];
    }

    private function writeProgressStep(string $prefix, SymfonyStyle $style, ProgressStep $step): void
    {
        $progress = $this->progress[\hash('xxh3', (string) $step->context)]
            ??= $this->createProgressBar($prefix, $style);

        $progress->setMessage($this->messageOf($step));
        $progress->advance();
    }

    private function createProgressBar(string $prefix, SymfonyStyle $style): ProgressBar
    {
        $this->completeProgressSteps();

        $progress = $style->createProgressBar();
        $progress->clear();
        $progress->setFormat($prefix . '[%bar%] %message%');
        $progress->clear();

        return $progress;
    }

    private function writeInfoStep(string $prefix, SymfonyStyle $style, MessageStep $step): void
    {
        $verbosity = OutputInterface::VERBOSITY_NORMAL;

        if ($step->level > self::DEFAULT_ROOT_DEPTH + 1) {
            $verbosity = OutputInterface::VERBOSITY_VERBOSE;
        }

        if ($step->level > self::DEFAULT_ROOT_DEPTH) {
            $message = $prefix . '<fg=gray> · </>' . $this->messageOf($step);

            $style->writeln($message, $verbosity);

            return;
        }

        $message = $prefix . '<info> · </info>' . $this->messageOf($step);

        $style->writeln($message, $verbosity);
    }

    private function writeNotifyStep(string $prefix, SymfonyStyle $style, MessageStep $step): void
    {
        $message = $prefix . '  <fg=gray> ' . $this->messageOf($step) . '</>';
        $verbosity = OutputInterface::VERBOSITY_VERY_VERBOSE;

        $style->writeln($message, $verbosity);
    }

    private function writeMessageStep(string $prefix, SymfonyStyle $style, MessageStep $step): void
    {
        $message = $prefix . '   <fg=gray>! </>' . $this->messageOf($step);
        $verbosity = OutputInterface::VERBOSITY_DEBUG;

        $style->writeln($message, $verbosity);
    }

    private function messageOf(MessageStep $step): string
    {
        $arguments = [];

        foreach ($step->arguments as $argument) {
            $arguments[] = '<comment>' . (string) $argument . '</comment>';
        }

        return \vsprintf($step->message, $arguments);
    }
}
