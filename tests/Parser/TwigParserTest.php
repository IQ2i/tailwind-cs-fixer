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

namespace TailwindCsFixer\Tests\Parser;

use PHPUnit\Framework\TestCase;
use TailwindCsFixer\Parser\TwigParser;
use TailwindCsFixer\Sorter\TailwindClassSorter;

class TwigParserTest extends TestCase
{
    private TwigParser $parser;

    protected function setUp(): void
    {
        $sorter = new TailwindClassSorter();
        $this->parser = new TwigParser($sorter);
    }

    public function testParseStaticClasses(): void
    {
        $input = '<div class="text-center p-4 bg-blue-500">Content</div>';
        $expected = '<div class="bg-blue-500 p-4 text-center">Content</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseWithTwigVariable(): void
    {
        $input = '<div class="{{ customClass }}">Content</div>';
        $expected = '<div class="{{ customClass }}">Content</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseMixedStaticAndTwig(): void
    {
        $input = '<div class="p-4 {{ customClass }} bg-white">Content</div>';
        $expected = '<div class="p-4 {{ customClass }} bg-white">Content</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseTwigAtBeginning(): void
    {
        $input = '<div class="{{ customClass }} text-center p-4 bg-blue-500">Content</div>';
        $expected = '<div class="{{ customClass }} bg-blue-500 p-4 text-center">Content</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseTwigAtEnd(): void
    {
        $input = '<div class="text-center p-4 bg-blue-500 {{ customClass }}">Content</div>';
        $expected = '<div class="bg-blue-500 p-4 text-center {{ customClass }}">Content</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseMultipleTwigExpressions(): void
    {
        $input = '<div class="{{ class1 }} p-4 {{ class2 }} bg-white">Content</div>';
        $expected = '<div class="{{ class1 }} p-4 {{ class2 }} bg-white">Content</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseWithSingleQuotes(): void
    {
        $input = "<div class='text-center p-4 bg-blue-500'>Content</div>";
        $expected = "<div class='bg-blue-500 p-4 text-center'>Content</div>";

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseMixedWithSingleQuotes(): void
    {
        $input = "<div class='p-4 {{ customClass }} bg-white'>Content</div>";
        $expected = "<div class='p-4 {{ customClass }} bg-white'>Content</div>";

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseComplexTwig(): void
    {
        $input = <<<TWIG
            <div class="flex justify-between items-center p-6 bg-white">
                <button class="hover:bg-blue-700 bg-blue-500 {{ buttonClass }} text-white px-4 py-2">
                    {{ buttonText }}
                </button>
            </div>
            TWIG;

        $expected = <<<TWIG
            <div class="flex items-center justify-between bg-white p-6">
                <button class="bg-blue-500 hover:bg-blue-700 {{ buttonClass }} px-4 py-2 text-white">
                    {{ buttonText }}
                </button>
            </div>
            TWIG;

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseEmptyString(): void
    {
        $this->assertEquals('', $this->parser->parse(''));
    }

    public function testParseWithoutClassAttribute(): void
    {
        $input = '<div>Content {{ variable }}</div>';
        $expected = '<div>Content {{ variable }}</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseAlreadySortedWithTwig(): void
    {
        $input = '<div class="bg-white p-4 {{ customClass }}">Content</div>';
        $expected = '<div class="bg-white p-4 {{ customClass }}">Content</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseOnlyStaticClassesBeforeTwig(): void
    {
        $input = '<div class="text-center p-4 bg-blue-500 {{ custom }}">Content</div>';
        $expected = '<div class="bg-blue-500 p-4 text-center {{ custom }}">Content</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseOnlyStaticClassesAfterTwig(): void
    {
        $input = '<div class="{{ custom }} text-center p-4 bg-blue-500">Content</div>';
        $expected = '<div class="{{ custom }} bg-blue-500 p-4 text-center">Content</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseMultipleElements(): void
    {
        $input = <<<TWIG
            <div class="p-4 {{ container }}">
                <span class="font-bold text-lg">Text</span>
                <p class="{{ para }} text-base">Content</p>
            </div>
            TWIG;

        $expected = <<<TWIG
            <div class="p-4 {{ container }}">
                <span class="text-lg font-bold">Text</span>
                <p class="{{ para }} text-base">Content</p>
            </div>
            TWIG;

        $this->assertEquals($expected, $this->parser->parse($input));
    }
}
