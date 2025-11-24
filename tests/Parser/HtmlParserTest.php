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
use TailwindCsFixer\Parser\HtmlParser;
use TailwindCsFixer\Sorter\TailwindClassSorter;

class HtmlParserTest extends TestCase
{
    private HtmlParser $parser;

    protected function setUp(): void
    {
        $sorter = new TailwindClassSorter();
        $this->parser = new HtmlParser($sorter);
    }

    public function testParseWithDoubleQuotes(): void
    {
        $input = '<div class="text-center p-4 bg-blue-500">Content</div>';
        $expected = '<div class="bg-blue-500 p-4 text-center">Content</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseWithSingleQuotes(): void
    {
        $input = "<div class='text-center p-4 bg-blue-500'>Content</div>";
        $expected = "<div class='bg-blue-500 p-4 text-center'>Content</div>";

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseMultipleElements(): void
    {
        $input = '<div class="p-4 text-center"><span class="text-lg font-bold">Text</span></div>';
        $expected = '<div class="p-4 text-center"><span class="font-bold text-lg">Text</span></div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseAlreadySorted(): void
    {
        $input = '<div class="bg-blue-500 p-4 text-center">Content</div>';
        $expected = '<div class="bg-blue-500 p-4 text-center">Content</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseWithoutClassAttribute(): void
    {
        $input = '<div>Content</div>';
        $expected = '<div>Content</div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseEmptyString(): void
    {
        $this->assertEquals('', $this->parser->parse(''));
    }

    public function testParseMixedQuotes(): void
    {
        $input = '<div class="p-4 text-center"><span class=\'text-lg font-bold\'>Text</span></div>';
        $expected = '<div class="p-4 text-center"><span class=\'font-bold text-lg\'>Text</span></div>';

        $this->assertEquals($expected, $this->parser->parse($input));
    }

    public function testParseComplexHtml(): void
    {
        $input = <<<HTML
            <!DOCTYPE html>
            <html>
            <body>
                <div class="flex justify-between items-center p-6 bg-white">
                    <h1 class="text-xl font-bold">Title</h1>
                    <button class="hover:bg-blue-700 bg-blue-500 text-white px-4 py-2">Click</button>
                </div>
            </body>
            </html>
            HTML;

        $expected = <<<HTML
            <!DOCTYPE html>
            <html>
            <body>
                <div class="flex items-center justify-between bg-white p-6">
                    <h1 class="font-bold text-xl">Title</h1>
                    <button class="bg-blue-500 hover:bg-blue-700 px-4 py-2 text-white">Click</button>
                </div>
            </body>
            </html>
            HTML;

        $this->assertEquals($expected, $this->parser->parse($input));
    }
}
