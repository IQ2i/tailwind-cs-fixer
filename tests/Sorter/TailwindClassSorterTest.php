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

    // ========================================
    // 1. LAYOUT - Position, Display, Z-index, Overflow
    // ========================================

    public function testSortLayoutClasses(): void
    {
        $input = 'z-50 static block relative';
        $expected = 'relative static z-50 block';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortPositionClasses(): void
    {
        $input = 'left-0 top-0 bottom-0 right-0 inset-0';
        $expected = 'inset-0 top-0 right-0 bottom-0 left-0';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortDisplayClasses(): void
    {
        $input = 'inline-block hidden block flex inline grid';
        $expected = 'block flex grid hidden inline inline-block';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortOverflowClasses(): void
    {
        $input = 'overflow-x-auto overflow-hidden overflow-auto';
        $expected = 'overflow-auto overflow-hidden overflow-x-auto';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 2. FLEXBOX & GRID - Flex, Grid, Items, Justify, Gap
    // ========================================

    public function testSortFlexboxClasses(): void
    {
        $input = 'justify-center items-center flex-col flex';
        $expected = 'flex flex-col items-center justify-center';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortFlexDirectionAndWrap(): void
    {
        $input = 'flex-wrap flex-row-reverse flex';
        $expected = 'flex flex-row-reverse flex-wrap';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortGridClasses(): void
    {
        $input = 'gap-8 grid md:grid-cols-3 grid-cols-1';
        $expected = 'grid grid-cols-1 gap-8 md:grid-cols-3';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortGridComplexLayout(): void
    {
        $input = 'grid-rows-3 grid-flow-col gap-y-4 gap-x-2 grid grid-cols-4';
        $expected = 'grid grid-flow-col grid-cols-4 grid-rows-3 gap-x-2 gap-y-4';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 3. SPACING - Margin, Padding, Space Between
    // ========================================

    public function testSortSpacingClasses(): void
    {
        $input = 'p-4 m-2 px-6 mx-auto';
        $expected = 'm-2 mx-auto p-4 px-6';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortMarginClasses(): void
    {
        $input = 'ml-4 mt-2 mr-8 mb-4 mx-auto my-6';
        $expected = 'mx-auto my-6 mt-2 mr-8 mb-4 ml-4';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortPaddingClasses(): void
    {
        $input = 'pt-2 p-4 pb-8 px-6 py-3';
        $expected = 'p-4 px-6 py-3 pt-2 pb-8';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortSpaceBetweenClasses(): void
    {
        $input = 'space-x-6 flex space-y-4';
        $expected = 'flex space-x-6 space-y-4';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 4. SIZING - Width, Height, Min/Max
    // ========================================

    public function testSortSizingClasses(): void
    {
        $input = 'h-24 w-full max-w-4xl min-h-screen';
        $expected = 'h-24 min-h-screen w-full max-w-4xl';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortWidthClasses(): void
    {
        $input = 'max-w-7xl w-full min-w-0 w-1/2';
        $expected = 'w-1/2 w-full max-w-7xl min-w-0';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortHeightClasses(): void
    {
        $input = 'max-h-screen h-12 min-h-0 h-full';
        $expected = 'h-12 h-full max-h-screen min-h-0';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortWidthAndHeightTogether(): void
    {
        $input = 'text-blue-600 w-6 h-6';
        $expected = 'h-6 w-6 text-blue-600';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 5. TYPOGRAPHY - Font, Text, Line Height, Letter Spacing
    // ========================================

    public function testSortTypographyClasses(): void
    {
        $input = 'text-center font-bold text-2xl leading-tight';
        $expected = 'text-center text-2xl leading-tight font-bold';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortTextSizeAndWeight(): void
    {
        $input = 'font-bold text-blue-600 text-2xl';
        $expected = 'text-2xl font-bold text-blue-600';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortTextAlignment(): void
    {
        $input = 'text-right text-center text-left';
        $expected = 'text-center text-left text-right';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortTextDecoration(): void
    {
        $input = 'no-underline underline line-through';
        $expected = 'underline line-through no-underline';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortTextTransform(): void
    {
        $input = 'lowercase uppercase capitalize normal-case';
        $expected = 'uppercase lowercase capitalize normal-case';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 6. BACKGROUNDS - Background Color, Gradient
    // ========================================

    public function testSortBackgroundClasses(): void
    {
        $input = 'bg-blue-500 bg-opacity-50 bg-cover';
        $expected = 'bg-opacity-50 bg-blue-500 bg-cover';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortGradientClasses(): void
    {
        $input = 'bg-gradient-to-r py-20 from-blue-500 to-purple-600';
        $expected = 'bg-gradient-to-r from-blue-500 to-purple-600 py-20';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 7. BORDERS - Border Width, Style, Color, Radius
    // ========================================

    public function testSortBorderClasses(): void
    {
        $input = 'border-gray-300 border-2 border rounded-lg';
        $expected = 'rounded-lg border border-2 border-gray-300';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortBorderDirectional(): void
    {
        $input = 'border-l border-t border';
        $expected = 'border border-t border-l';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortBorderRadius(): void
    {
        $input = 'rounded-t rounded-lg rounded';
        $expected = 'rounded rounded-lg rounded-t';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortDivideClasses(): void
    {
        $input = 'divide-gray-200 divide-y divide-x';
        $expected = 'divide-x divide-y divide-gray-200';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 8. EFFECTS - Shadow, Opacity, Blur, Mix Blend
    // ========================================

    public function testSortEffectClasses(): void
    {
        $input = 'opacity-50 shadow-xl blur-sm';
        $expected = 'shadow-xl opacity-50 blur-sm';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortShadowClasses(): void
    {
        $input = 'shadow-2xl shadow shadow-md';
        $expected = 'shadow shadow-md shadow-2xl';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 9. TRANSITIONS & ANIMATIONS
    // ========================================

    public function testSortTransitionClasses(): void
    {
        $input = 'ease-in-out duration-300 transition-all delay-100';
        $expected = 'transition-all delay-100 duration-300 ease-in-out';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortAnimationClasses(): void
    {
        $input = 'animate-bounce animate-spin animate-pulse';
        $expected = 'animate-bounce animate-pulse animate-spin';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 10. TRANSFORMS - Scale, Rotate, Translate
    // ========================================

    public function testSortTransformClasses(): void
    {
        $input = 'translate-x-4 scale-110 rotate-45 transform';
        $expected = 'transform scale-110 rotate-45 translate-x-4';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 11. INTERACTIVITY - Cursor, Pointer Events, User Select
    // ========================================

    public function testSortInteractivityClasses(): void
    {
        $input = 'select-none pointer-events-none cursor-pointer';
        $expected = 'cursor-pointer pointer-events-none select-none';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 12. VARIANTS - State Modifiers (hover, focus, active)
    // ========================================

    public function testSortWithStateVariants(): void
    {
        $input = 'bg-blue-500 hover:bg-blue-700 text-white';
        $expected = 'bg-blue-500 text-white hover:bg-blue-700';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortMultipleStateVariants(): void
    {
        $input = 'transition-colors hover:text-blue-600 text-gray-700';
        $expected = 'text-gray-700 transition-colors hover:text-blue-600';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortFocusVariants(): void
    {
        $input = 'focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border-gray-300 border';
        $expected = 'border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortTransitionWithHover(): void
    {
        $input = 'hover:shadow-xl transition-shadow';
        $expected = 'transition-shadow hover:shadow-xl';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 13. VARIANTS - Responsive Modifiers
    // ========================================

    public function testSortWithResponsiveVariants(): void
    {
        $input = 'text-base md:text-lg p-4 md:p-8';
        $expected = 'p-4 text-base md:p-8 md:text-lg';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortResponsiveGrid(): void
    {
        $input = 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4';
        $expected = 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortComplexResponsive(): void
    {
        $input = 'lg:text-2xl md:text-xl text-base sm:text-lg';
        $expected = 'text-base sm:text-lg md:text-xl lg:text-2xl';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 14. ARBITRARY VALUES
    // ========================================

    public function testSortWithArbitraryValues(): void
    {
        $input = 'text-center w-[42px] p-4';
        $expected = 'w-[42px] p-4 text-center';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortArbitraryColors(): void
    {
        $input = 'bg-[#1da1f2] p-4 text-white';
        $expected = 'bg-[#1da1f2] p-4 text-white';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 15. NUMERIC ORDER WITHIN SAME PREFIX
    // ========================================

    public function testSortPreservesNumericOrder(): void
    {
        $input = 'p-8 p-2 p-4';
        $expected = 'p-2 p-4 p-8';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortFractionOrder(): void
    {
        $input = 'w-1/2 w-1/4 w-3/4';
        $expected = 'w-1/4 w-1/2 w-3/4';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 16. COMPLEX REAL-WORLD EXAMPLES
    // ========================================

    public function testSortComplexCard(): void
    {
        $input = 'hover:shadow-xl transition-shadow rounded-lg shadow-md bg-white p-6 border';
        $expected = 'rounded-lg border bg-white p-6 shadow-md transition-shadow hover:shadow-xl';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortComplexButton(): void
    {
        $input = 'hover:bg-blue-700 transition-colors bg-blue-600 px-8 rounded-lg text-white py-3 font-semibold';
        $expected = 'rounded-lg bg-blue-600 px-8 py-3 font-semibold text-white transition-colors hover:bg-blue-700';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortComplexNav(): void
    {
        $input = 'px-4 mx-auto max-w-7xl py-3 flex justify-between items-center';
        $expected = 'mx-auto flex max-w-7xl items-center justify-between px-4 py-3';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortComplexHeader(): void
    {
        $input = 'shadow-lg sticky top-0 z-50 bg-white border-b';
        $expected = 'sticky top-0 z-50 border-b bg-white shadow-lg';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortComplexForm(): void
    {
        $input = 'focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border-gray-300 border rounded-lg px-4 w-full py-2';
        $expected = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortComplexContainer(): void
    {
        $input = 'text-center px-4 mx-auto max-w-4xl';
        $expected = 'mx-auto max-w-4xl px-4 text-center';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortComplexGrid(): void
    {
        $input = 'hover:shadow-lg transition duration-300 rounded-lg p-6 bg-white shadow-md flex items-center justify-between';
        $expected = 'flex items-center justify-between rounded-lg bg-white p-6 shadow-md transition duration-300 hover:shadow-lg';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 17. EDGE CASES
    // ========================================

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

    public function testSortSingleClass(): void
    {
        $input = 'flex';
        $expected = 'flex';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 18. DARK MODE VARIANTS
    // ========================================

    public function testSortDarkModeBasic(): void
    {
        $input = 'dark:text-white text-black';
        $expected = 'text-black dark:text-white';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortDarkModeMultipleClasses(): void
    {
        $input = 'dark:bg-gray-900 bg-white dark:text-white text-black';
        $expected = 'bg-white text-black dark:bg-gray-900 dark:text-white';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortDarkModeWithHover(): void
    {
        $input = 'dark:hover:text-gray-200 dark:text-white';
        $expected = 'dark:text-white dark:hover:text-gray-200';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortDarkModeWithMultipleStateVariants(): void
    {
        $input = 'dark:hover:bg-gray-800 hover:bg-gray-100 bg-white dark:bg-gray-900';
        $expected = 'bg-white hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-800';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortDarkModeWithFocus(): void
    {
        $input = 'dark:focus:ring-blue-500 focus:ring-blue-300 dark:focus:border-blue-400';
        $expected = 'focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortDarkModeComplex(): void
    {
        $input = 'p-4 dark:hover:bg-gray-800 hover:bg-gray-100 dark:bg-gray-900 bg-white dark:text-white text-gray-900';
        $expected = 'bg-white p-4 text-gray-900 hover:bg-gray-100 dark:bg-gray-900 dark:text-white dark:hover:bg-gray-800';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    // ========================================
    // 19. COMPREHENSIVE MODIFIER COMBINATIONS
    // ========================================

    public function testSortHoverFocusModifiers(): void
    {
        $input = 'focus:text-gray-300 hover:text-gray-200 text-white';
        $expected = 'text-white hover:text-gray-200 focus:text-gray-300';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortResponsiveWithHover(): void
    {
        $input = 'lg:hover:text-blue-700 hover:text-blue-600 lg:text-blue-500 text-blue-400';
        $expected = 'text-blue-400 hover:text-blue-600 lg:text-blue-500 lg:hover:text-blue-700';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortMultipleResponsiveBreakpoints(): void
    {
        $input = 'lg:text-xl sm:text-lg text-base md:text-lg';
        $expected = 'text-base sm:text-lg md:text-lg lg:text-xl';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortResponsivePaddingVariants(): void
    {
        $input = 'lg:p-8 sm:p-6 p-4 hover:p-6';
        $expected = 'p-4 hover:p-6 sm:p-6 lg:p-8';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortCompleteVariantExample(): void
    {
        $input = 'text-white hover:text-gray-200 focus:text-gray-300 lg:text-xl sm:text-lg';
        $expected = 'text-white hover:text-gray-200 focus:text-gray-300 sm:text-lg lg:text-xl';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortDarkModeResponsiveCombination(): void
    {
        $input = 'dark:lg:text-2xl lg:text-xl dark:text-white text-black';
        $expected = 'text-black lg:text-xl dark:text-white dark:lg:text-2xl';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortAllModifierTypes(): void
    {
        $input = 'dark:lg:hover:bg-gray-700 lg:hover:bg-gray-200 dark:bg-gray-900 bg-white hover:bg-gray-100 lg:bg-gray-50';
        $expected = 'bg-white hover:bg-gray-100 lg:bg-gray-50 lg:hover:bg-gray-200 dark:bg-gray-900 dark:lg:hover:bg-gray-700';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortTestVariantsFromHtml(): void
    {
        // Test case from test-variants.html line 1
        $input = 'text-white hover:text-gray-200 focus:text-gray-300 lg:text-xl sm:text-lg';
        $expected = 'text-white hover:text-gray-200 focus:text-gray-300 sm:text-lg lg:text-xl';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortDarkHoverFromHtml(): void
    {
        // Test case from test-variants.html line 2
        $input = 'dark:text-white dark:hover:text-gray-200';
        $expected = 'dark:text-white dark:hover:text-gray-200';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortDarkBgHoverFromHtml(): void
    {
        // Test case from test-variants.html line 3
        $input = 'bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800';
        $expected = 'bg-white hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-800';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }

    public function testSortPaddingVariantsFromHtml(): void
    {
        // Test case from test-variants.html line 4
        $input = 'p-4 hover:p-6 lg:p-8 sm:p-6';
        $expected = 'p-4 hover:p-6 sm:p-6 lg:p-8';

        $this->assertEquals($expected, $this->sorter->sort($input));
    }
}
