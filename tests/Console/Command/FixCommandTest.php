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

namespace TailwindCsFixer\Tests\Console\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TailwindCsFixer\Console\Command\FixCommand;

class FixCommandTest extends TestCase
{
    private string $fixturesDir;
    private string $tempDir;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->fixturesDir = __DIR__.'/../../Fixtures';
        $this->tempDir = \sys_get_temp_dir().'/tailwind-cs-fixer-test-'.\uniqid();
        \mkdir($this->tempDir, 0o777, true);

        $application = new Application();
        $application->add(new FixCommand());
        $command = $application->find('fix');
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        if (\is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testFixHtmlFile(): void
    {
        $sourceFile = $this->fixturesDir.'/sample.html';
        $targetFile = $this->tempDir.'/sample.html';
        \copy($sourceFile, $targetFile);

        $this->commandTester->execute([
            'path' => $targetFile,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('sample.html', $output);
        $this->assertStringContainsString('Fixed 1 file(s)', $output);
        $this->assertSame(0, $this->commandTester->getStatusCode());

        $content = \file_get_contents($targetFile);
        $this->assertStringContainsString('class="rounded-lg bg-blue-500 p-4 text-center shadow-md"', $content);
        $this->assertStringContainsString('class="text-2xl font-bold text-white"', $content);
    }

    public function testFixTwigFile(): void
    {
        $sourceFile = $this->fixturesDir.'/sample.twig';
        $targetFile = $this->tempDir.'/sample.twig';
        \copy($sourceFile, $targetFile);

        $this->commandTester->execute([
            'path' => $targetFile,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('sample.twig', $output);
        $this->assertStringContainsString('Fixed 1 file(s)', $output);
        $this->assertSame(0, $this->commandTester->getStatusCode());

        $content = \file_get_contents($targetFile);
        $this->assertStringContainsString('class="flex items-center justify-between bg-white p-6 shadow-lg"', $content);
        $this->assertStringContainsString('class="text-xl font-bold {{ titleClass }}"', $content);
    }

    public function testFixDirectory(): void
    {
        \copy($this->fixturesDir.'/sample.html', $this->tempDir.'/sample.html');
        \copy($this->fixturesDir.'/sample.twig', $this->tempDir.'/sample.twig');

        $this->commandTester->execute([
            'path' => $this->tempDir,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Processing 2 file(s)', $output);
        $this->assertStringContainsString('Fixed 2 file(s)', $output);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testDryRunDoesNotModifyFiles(): void
    {
        $sourceFile = $this->fixturesDir.'/sample.html';
        $targetFile = $this->tempDir.'/sample.html';
        \copy($sourceFile, $targetFile);

        $originalContent = \file_get_contents($targetFile);

        $this->commandTester->execute([
            'path' => $targetFile,
            '--dry-run' => true,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('DRY RUN', $output);
        $this->assertStringContainsString('sample.html', $output);
        $this->assertSame(0, $this->commandTester->getStatusCode());

        $contentAfter = \file_get_contents($targetFile);
        $this->assertEquals($originalContent, $contentAfter);
    }

    public function testDiffOption(): void
    {
        $sourceFile = $this->fixturesDir.'/sample.html';
        $targetFile = $this->tempDir.'/sample.html';
        \copy($sourceFile, $targetFile);

        $this->commandTester->execute([
            'path' => $targetFile,
            '--diff' => true,
            '--dry-run' => true,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('-', $output);
        $this->assertStringContainsString('+', $output);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testNonExistentPath(): void
    {
        $this->commandTester->execute([
            'path' => '/non/existent/path',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('does not exist', $output);
        $this->assertSame(1, $this->commandTester->getStatusCode());
    }

    public function testNoFilesFound(): void
    {
        $emptyDir = $this->tempDir.'/empty';
        \mkdir($emptyDir);

        $this->commandTester->execute([
            'path' => $emptyDir,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('No files found', $output);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testCustomExtensions(): void
    {
        \copy($this->fixturesDir.'/sample.html', $this->tempDir.'/sample.html');
        \copy($this->fixturesDir.'/sample.twig', $this->tempDir.'/sample.twig');

        $this->commandTester->execute([
            'path' => $this->tempDir,
            '--extensions' => 'html',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Processing 1 file(s)', $output);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testAlreadySortedFile(): void
    {
        $sourceFile = $this->fixturesDir.'/already-sorted.html';
        $targetFile = $this->tempDir.'/already-sorted.html';
        \copy($sourceFile, $targetFile);

        $this->commandTester->execute([
            'path' => $targetFile,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Fixed 0 file(s)', $output);
        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testDefaultPathIsCurrent(): void
    {
        $originalDir = \getcwd();
        \chdir($this->tempDir);

        \copy($this->fixturesDir.'/sample.html', $this->tempDir.'/sample.html');

        try {
            $this->commandTester->execute([]);

            $output = $this->commandTester->getDisplay();
            $this->assertStringContainsString('Processing', $output);
            $this->assertSame(0, $this->commandTester->getStatusCode());
        } finally {
            \chdir($originalDir);
        }
    }

    private function removeDirectory(string $dir): void
    {
        $files = \array_diff(\scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            \is_dir($path) ? $this->removeDirectory($path) : \unlink($path);
        }
        \rmdir($dir);
    }
}
