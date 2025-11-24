<?php

declare(strict_types=1);

/*
 * This file is part of the Tailwind-CS-Fixer package.
 *
 * (c) Loïc Sapone <loic@sapone.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TailwindCsFixer\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use TailwindCsFixer\Parser\HtmlParser;
use TailwindCsFixer\Parser\TwigParser;
use TailwindCsFixer\Sorter\TailwindClassSorter;

#[AsCommand(name: 'fix', description: 'Fixes a directory or a file.')]
class FixCommand extends Command
{
    private TailwindClassSorter $sorter;
    private HtmlParser $htmlParser;
    private TwigParser $twigParser;

    public function __construct()
    {
        parent::__construct();

        $this->sorter = new TailwindClassSorter();
        $this->htmlParser = new HtmlParser($this->sorter);
        $this->twigParser = new TwigParser($this->sorter);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to fix (file or directory)', '.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be fixed without writing')
            ->addOption('diff', null, InputOption::VALUE_NONE, 'Show diff of changes')
            ->addOption('extensions', null, InputOption::VALUE_REQUIRED, 'File extensions to process (comma-separated)', 'html,twig')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');
        $dryRun = $input->getOption('dry-run');
        $showDiff = $input->getOption('diff');
        $extensions = \explode(',', $input->getOption('extensions'));

        if (!\file_exists($path)) {
            $io->error("Path does not exist: $path");

            return Command::FAILURE;
        }

        $files = $this->findFiles($path, $extensions);
        $filesArray = \is_array($files) ? $files : \iterator_to_array($files);

        if (0 === \count($filesArray)) {
            $io->warning('No files found to process');

            return Command::SUCCESS;
        }

        $io->title('Tailwind CS Fixer');
        $io->text(\sprintf('Processing %d file(s)...', \count($filesArray)));

        if ($dryRun) {
            $io->note('DRY RUN - No files will be modified');
        }

        $fixedCount = 0;
        $errorCount = 0;

        foreach ($filesArray as $file) {
            try {
                $result = $this->processFile($file, $dryRun, $showDiff);

                if ($result['changed']) {
                    ++$fixedCount;
                    $io->text(\sprintf('<info>✓</info> %s', $this->getDisplayPath($file)));

                    if ($showDiff && isset($result['diff'])) {
                        $io->block((string) $result['diff'], null, 'fg=yellow', ' ', true);
                    }
                }
            } catch (\Exception $e) {
                ++$errorCount;
                $io->text(\sprintf('<error>✗</error> %s: %s', $this->getDisplayPath($file), $e->getMessage()));
            }
        }

        $io->newLine();
        $io->success(\sprintf(
            'Fixed %d file(s)%s',
            $fixedCount,
            $errorCount > 0 ? \sprintf(' (%d error(s))', $errorCount) : ''
        ));

        return Command::SUCCESS;
    }

    private function findFiles(string $path, array $extensions): iterable
    {
        $finder = new Finder();

        if (\is_file($path)) {
            $extension = \pathinfo($path, \PATHINFO_EXTENSION);

            if (!\in_array($extension, \array_map('trim', $extensions))) {
                return [];
            }

            return $finder->append([$path]);
        }

        $finder->files()->in($path);

        foreach ($extensions as $ext) {
            $finder->name('*.'.\trim($ext));
        }

        $finder->exclude(['vendor', 'node_modules', 'var', 'cache']);

        return $finder;
    }

    private function processFile(\SplFileInfo $file, bool $dryRun, bool $showDiff): array
    {
        $originalContent = \file_get_contents($file->getPathname());
        $extension = $file->getExtension();

        $newContent = match ($extension) {
            'twig' => $this->twigParser->parse($originalContent),
            'html' => $this->htmlParser->parse($originalContent),
            default => $originalContent,
        };

        $changed = $originalContent !== $newContent;

        $result = ['changed' => $changed];

        if ($changed && !$dryRun) {
            \file_put_contents($file->getPathname(), $newContent);
        }

        if ($changed && $showDiff) {
            $result['diff'] = $this->generateDiff($originalContent, $newContent);
        }

        return $result;
    }

    private function generateDiff(string $original, string $new): string
    {
        $originalLines = \explode("\n", $original);
        $newLines = \explode("\n", $new);

        $diff = [];
        $maxLines = \max(\count($originalLines), \count($newLines));

        for ($i = 0; $i < $maxLines; ++$i) {
            $origLine = $originalLines[$i] ?? '';
            $newLine = $newLines[$i] ?? '';

            if ($origLine !== $newLine) {
                if (!empty($origLine)) {
                    $diff[] = '- '.$origLine;
                }
                if (!empty($newLine)) {
                    $diff[] = '+ '.$newLine;
                }
            }
        }

        return \implode("\n", \array_slice($diff, 0, 10));
    }

    private function getDisplayPath(\SplFileInfo $file): string
    {
        if (\method_exists($file, 'getRelativePathname')) {
            return $file->getRelativePathname();
        }

        return \basename($file->getPathname());
    }
}
