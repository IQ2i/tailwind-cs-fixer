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

namespace TailwindCsFixer\Tests\Sorter;

use PHPUnit\Framework\TestCase;
use TailwindCsFixer\Sorter\TailwindClassSorter;

class TailwindClassSorterTest extends TestCase
{
    private TailwindClassSorter $sorter;

    protected function setUp(): void
    {
        $this->sorter = new TailwindClassSorter();
    }

    public function testSortSimpleClasses(): void
    {
        $input = 'text-center p-4 bg-blue-500';
        $expected = 'bg-blue-500 p-4 text-center';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortWithSpacing(): void
    {
        $input = 'pt-2 p-4 pb-8';
        $expected = 'p-4 pt-2 pb-8';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortWithResponsiveVariants(): void
    {
        $input = 'text-base md:text-lg p-4 md:p-8';
        $expected = 'p-4 md:p-8 text-base md:text-lg';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortWithStateVariants(): void
    {
        $input = 'bg-blue-500 hover:bg-blue-700 text-white';
        $expected = 'bg-blue-500 hover:bg-blue-700 text-white';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortWithArbitraryValues(): void
    {
        $input = 'text-center w-[42px] p-4';
        $expected = 'w-[42px] p-4 text-center';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortPreservesNumericOrder(): void
    {
        $input = 'p-8 p-2 p-4';
        $expected = 'p-2 p-4 p-8';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortEmptyString(): void
    {
        $this->assertEquals('', $this->sorter->sort(''));
    }

    public function testSortNormalizesWhitespace(): void
    {
        $input = '  p-4   bg-blue-500  text-center  ';
        $expected = 'bg-blue-500 p-4 text-center';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortComplexExample(): void
    {
        $input = 'hover:shadow-lg transition duration-300 rounded-lg p-6 bg-white shadow-md flex items-center justify-between';
        $expected = 'flex items-center justify-between bg-white p-6 rounded-lg shadow-md transition duration-300 hover:shadow-lg';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortWithDirectionalBorders(): void
    {
        $input = 'border-l border-t border';
        $expected = 'border border-t border-l';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortFlexboxClasses(): void
    {
        $input = 'justify-center items-center flex-col flex';
        $expected = 'flex flex-col items-center justify-center';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }
}
