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

namespace TailwindCsFixer\Sorter;

class TailwindClassSorter
{
    /**
     * Class order based on Tailwind's official Prettier plugin
     * Follows the box model: Layout → Flexbox/Grid → Spacing → Sizing → Typography → Backgrounds → Borders → Effects → Transitions → Transforms → Interactivity.
     */
    private const CLASS_ORDER = [
        // Container
        'container',

        // Box Sizing
        'box-border', 'box-content',

        // Position (before display!) - Tailwind CSS order: static, fixed, absolute, relative, sticky
        'static', 'fixed', 'absolute', 'relative', 'sticky',
        'inset', 'inset-x', 'inset-y', 'inset-s', 'inset-e', 'bottom', 'left', 'right', 'top',

        // Visibility
        'visible', 'invisible', 'collapse',

        // Z-Index
        'z',

        // Spacing - Margin (BEFORE display!) - directionals alphabetical: b, l, r, t
        'm', 'mx', 'my', 'mb', 'ml', 'mr', 'mt', 'ms', 'me', 'mbs', 'mbe',

        // Display - Tailwind CSS order
        'block', 'inline-block', 'inline', 'flex', 'inline-flex', 'grid', 'inline-grid', 'table', 'table-row', 'table-cell', 'flow-root', 'hidden',

        // Space Between (AFTER display!)
        'space-x', 'space-y',

        // Overflow
        'overflow', 'overflow-x', 'overflow-y',

        // Sizing - Height before Width! (base values, then max, then min)
        'h', 'max-h', 'min-h',
        'w', 'min-w', 'max-w',
        'size',
        'max-block', 'min-block',
        'max-inline', 'min-inline',

        // Flex Container
        'flex-row', 'flex-row-reverse', 'flex-col', 'flex-col-reverse',
        'flex-wrap', 'flex-wrap-reverse', 'flex-nowrap',
        'flex-grow', 'grow',       // v3: flex-grow-*, v4: grow-*
        'flex-shrink', 'shrink',   // v3: flex-shrink-*, v4: shrink-*
        'flex-none', 'flex-initial', 'flex-auto',
        'basis',                   // flex-basis (v3 & v4)

        // Grid Container
        'grid-flow',
        'grid-cols', 'grid-rows',
        'auto-cols', 'auto-rows',

        // Flex & Grid Item
        'place-content', 'place-items', 'place-self',
        'items-start', 'items-end', 'items-center', 'items-baseline', 'items-stretch',
        'content', 'justify-items', 'justify-self',
        'justify-start', 'justify-end', 'justify-center', 'justify-between', 'justify-around', 'justify-evenly',
        'align-content', 'align-items', 'align-self',
        'order',

        // Gap
        'gap', 'gap-x', 'gap-y',

        // Borders - Radius before width before color - directionals alphabetical
        'rounded', 'rounded-t', 'rounded-r', 'rounded-b', 'rounded-l',
        'rounded-tl', 'rounded-tr', 'rounded-br', 'rounded-bl',
        'rounded-s', 'rounded-e',
        'border', 'border-b', 'border-e', 'border-l', 'border-r', 'border-s', 'border-t', 'border-x', 'border-y',
        'border-bs', 'border-be',
        'border-solid', 'border-dashed', 'border-dotted', 'border-double', 'border-none',
        'divide', // divide-x, divide-y, divide-{color} handled by getDivideSubOrder
        'outline', 'outline-offset',
        'ring', 'ring-inset', 'ring-offset',

        // Backgrounds - Opacity before size/position before color
        'bg', // This covers bg-opacity, bg-cover, bg-contain, bg-fixed, and colors
        'from', 'via', 'to', // Gradient color stops

        // Spacing - Padding - directionals alphabetical: b, l, r, t
        'p', 'px', 'py', 'pb', 'pl', 'pr', 'pt', 'ps', 'pe', 'pbs', 'pbe',

        // Typography - Text (alignment, size) → Font → Leading → Text (opacity, color)
        'text', // This covers text-left, text-center, text-right, text-sm, text-lg, colors, etc.
        'font', // This covers font-bold, font-semibold, etc. (positioned fractionally via buildOrderMap)
        'font-variant-numeric',
        'leading', 'tracking',
        'uppercase', 'lowercase', 'capitalize', 'normal-case',
        'italic', 'not-italic',
        'underline', 'line-through', 'no-underline', 'overline',
        'decoration',
        'decoration-slice', 'decoration-clone',         // v3
        'box-decoration-slice', 'box-decoration-clone', // v4
        'antialiased', 'subpixel-antialiased',

        // Lists
        'list', 'list-inside', 'list-outside',

        // Whitespace
        'whitespace', 'break',

        // Effects - opacity before shadow
        'opacity',
        'shadow', 'shadow-inner', 'shadow-none',
        'mix-blend',
        'bg-blend',

        // Filters (alphabetical: blur, brightness, contrast, drop-shadow, grayscale, hue-rotate, invert, saturate, sepia)
        'blur', 'brightness', 'contrast', 'drop-shadow', 'grayscale', 'hue-rotate', 'invert', 'saturate', 'sepia',
        'backdrop-blur', 'backdrop-brightness', 'backdrop-contrast', 'backdrop-grayscale',
        'backdrop-hue-rotate', 'backdrop-invert', 'backdrop-opacity', 'backdrop-saturate', 'backdrop-sepia',

        // Tables
        'border-collapse', 'border-separate',
        'table-auto', 'table-fixed',

        // Transitions & Animation
        'transition', 'transition-all', 'transition-colors', 'transition-opacity', 'transition-shadow', 'transition-transform', 'transition-none',
        'delay',
        'duration',
        'ease', 'ease-in', 'ease-out', 'ease-in-out', 'ease-linear',
        'animate',

        // Transforms - Tailwind CSS order: translate, rotate, scale, skew, transform (base last)
        'translate', 'translate-x', 'translate-y',
        'rotate',
        'scale', 'scale-x', 'scale-y',
        'skew', 'skew-x', 'skew-y',
        'transform', 'transform-gpu', 'transform-none',
        'origin',

        // Interactivity - pointer-events before cursor
        'group', 'peer',
        'pointer-events',
        'cursor',
        'resize',
        'scroll',
        'select',
        'appearance',

        // SVG
        'fill', 'stroke',

        // Accessibility
        'sr',
    ];

    private array $orderMap = [];
    private array $responsiveBreakpoints = ['sm', 'md', 'lg', 'xl', '2xl'];
    private array $stateVariants = ['hover', 'focus', 'active', 'disabled', 'visited', 'checked', 'group-hover', 'focus-within', 'focus-visible'];
    private array $mediaVariants = ['dark'];

    public function __construct()
    {
        $this->buildOrderMap();
    }

    public function sort(string $classes): string
    {
        $classes = \trim(\preg_replace('/\s+/', ' ', $classes));

        if (empty($classes)) {
            return '';
        }

        $classList = \explode(' ', $classes);
        \usort($classList, [$this, 'compareClasses']);

        return \implode(' ', $classList);
    }

    private function compareClasses(string $a, string $b): int
    {
        // Extract variants and base classes
        $partsA = \explode(':', $a);
        $partsB = \explode(':', $b);

        $baseA = \end($partsA);
        $baseB = \end($partsB);

        $variantsA = \array_slice($partsA, 0, -1);
        $variantsB = \array_slice($partsB, 0, -1);

        // Compare variant types first (plain < state < responsive)
        $variantTypeA = $this->getVariantType($variantsA);
        $variantTypeB = $this->getVariantType($variantsB);

        if ($variantTypeA !== $variantTypeB) {
            return $variantTypeA <=> $variantTypeB;
        }

        // Within same variant type, compare responsive breakpoints order
        if (2 === $variantTypeA) { // Both are responsive
            $breakpointOrderA = $this->getResponsiveOrder($variantsA);
            $breakpointOrderB = $this->getResponsiveOrder($variantsB);

            if ($breakpointOrderA !== $breakpointOrderB) {
                return $breakpointOrderA <=> $breakpointOrderB;
            }
        }

        // Compare base class order
        $orderA = $this->getClassOrder($baseA);
        $orderB = $this->getClassOrder($baseB);

        $diff = $orderA - $orderB;
        if (\abs($diff) > 0.0001) {
            return $diff > 0 ? 1 : -1;
        }

        // Same base order, compare numeric values
        return $this->compareNumericValues($a, $b);
    }

    private function getVariantType(array $variants): int
    {
        if (empty($variants)) {
            return 0; // Plain utility (no variants)
        }

        $hasMedia = false;
        $hasResponsive = false;
        $hasState = false;

        // Check which types of variants are present
        foreach ($variants as $variant) {
            if (\in_array($variant, $this->mediaVariants)) {
                $hasMedia = true;
            } elseif (\in_array($variant, $this->responsiveBreakpoints)) {
                $hasResponsive = true;
            } elseif (\in_array($variant, $this->stateVariants)) {
                $hasState = true;
            }
        }

        // Determine variant type based on combination
        // Order: plain(0) < state(1) < responsive(2) < media(3) < media+state(4) < media+responsive(5)
        if ($hasMedia && $hasResponsive) {
            return 5; // Media + responsive (e.g., dark:lg:)
        }

        if ($hasMedia && $hasState) {
            return 4; // Media + state (e.g., dark:hover:)
        }

        if ($hasMedia) {
            return 3; // Media only (e.g., dark:)
        }

        if ($hasResponsive) {
            return 2; // Responsive (e.g., lg:)
        }

        if ($hasState) {
            return 1; // State (e.g., hover:)
        }

        return 0; // Other variants treated as plain
    }

    private function getResponsiveOrder(array $variants): int
    {
        foreach ($variants as $variant) {
            $index = \array_search($variant, $this->responsiveBreakpoints);
            if (false !== $index) {
                return $index;
            }
        }

        return 999;
    }

    private function getClassOrder(string $class): float
    {
        // Handle negative value classes (e.g. -mt-4, -translate-x-1/2)
        $lookupClass = \str_starts_with($class, '-') ? \substr($class, 1) : $class;

        // Try exact match first
        if (isset($this->orderMap[$lookupClass])) {
            return $this->orderMap[$lookupClass];
        }

        // Try prefix match
        $prefix = $this->extractPrefix($lookupClass);
        if (isset($this->orderMap[$prefix])) {
            $baseOrder = $this->orderMap[$prefix];

            // Add fractional sub-order for text classes to position them correctly relative to font/leading
            if ('text' === $prefix) {
                $subOrder = $this->getTextSubOrder($lookupClass);

                return $baseOrder + ($subOrder * 0.001);
            }

            return $baseOrder;
        }

        // Unknown classes go to the beginning (like prettier-plugin-tailwindcss)
        return -1;
    }

    private function extractPrefix(string $class): string
    {
        // Handle negative value classes (e.g. -mt-4, -translate-x-1/2)
        if (\str_starts_with($class, '-')) {
            $class = \substr($class, 1);
        }

        // Handle arbitrary values like w-[42px]
        if (\str_contains($class, '[')) {
            $bracketPos = \strpos($class, '[');
            $beforeBracket = \substr($class, 0, $bracketPos);

            // Remove trailing dash
            if (\str_ends_with($beforeBracket, '-')) {
                $beforeBracket = \substr($beforeBracket, 0, -1);
            }

            // For min-w-[...] or max-w-[...], return "min-w" or "max-w"
            if (\preg_match('/^(min|max)-(w|h)$/', $beforeBracket, $matches)) {
                return $matches[1].'-'.$matches[2];
            }

            $dashPos = \strrpos($beforeBracket, '-');
            if (false !== $dashPos) {
                return \substr($beforeBracket, 0, $dashPos);
            }

            return $beforeBracket;
        }

        // No dash means it's the class itself
        if (!\str_contains($class, '-')) {
            return $class;
        }

        $parts = \explode('-', $class);

        if (\count($parts) >= 2) {
            $firstPart = $parts[0];
            $secondPart = $parts[1] ?? '';

            // Handle min-* and max-* specially (min-w, max-w, min-h, max-h, min-block, max-block, min-inline, max-inline)
            if (('min' === $firstPart || 'max' === $firstPart) && \in_array($secondPart, ['w', 'h', 'block', 'inline'])) {
                return $firstPart.'-'.$secondPart;
            }

            // Handle directional utilities (border-t, rounded-tr, etc.)
            // BUT exclude 'divide' because divide-x and divide-y should have prefix 'divide', not 'divide-x'/'divide-y'
            if ('divide' !== $firstPart && \in_array($secondPart, ['t', 'r', 'b', 'l', 'x', 'y', 's', 'e', 'tl', 'tr', 'bl', 'br', 'ss', 'se', 'es', 'ee'])) {
                return $firstPart.'-'.$secondPart;
            }

            // Handle special cases
            if ('grid' === $firstPart && \in_array($secondPart, ['cols', 'rows', 'flow'])) {
                return $firstPart.'-'.$secondPart;
            }

            if ('auto' === $firstPart && \in_array($secondPart, ['cols', 'rows'])) {
                return $firstPart.'-'.$secondPart;
            }

            if ('flex' === $firstPart && \in_array($secondPart, ['row', 'col', 'wrap', 'grow', 'shrink', 'none', 'initial', 'auto'])) {
                return $firstPart.'-'.$secondPart;
            }

            // Handle pointer-events specially (pointer-events-none, pointer-events-auto, etc.)
            if ('pointer' === $firstPart && 'events' === $secondPart) {
                return 'pointer-events';
            }

            // Handle drop-shadow (v3: drop-shadow, v4: drop-shadow-xs/sm/md/lg/xl/2xl)
            if ('drop' === $firstPart && 'shadow' === $secondPart) {
                return 'drop-shadow';
            }

            // Handle box-decoration (v4: box-decoration-slice, box-decoration-clone)
            if ('box' === $firstPart && 'decoration' === $secondPart) {
                return 'box-decoration';
            }

            // Keep divide as a single prefix for all divide-* utilities
            // getDivideSubOrder will handle the internal ordering
            // (no special case here, just return 'divide')

            return $firstPart;
        }

        return $class;
    }

    private function compareNumericValues(string $a, string $b): int
    {
        // Extract base class without variants for sub-ordering
        $partsA = \explode(':', $a);
        $partsB = \explode(':', $b);
        $baseA = \end($partsA);
        $baseB = \end($partsB);

        // Special ordering for specific prefixes
        $prefixA = $this->extractPrefix($baseA);
        $prefixB = $this->extractPrefix($baseB);

        if ($prefixA === $prefixB) {
            // Special ordering for text-* classes
            if ('text' === $prefixA) {
                $orderA = $this->getTextSubOrder($baseA);
                $orderB = $this->getTextSubOrder($baseB);
                if ($orderA !== $orderB) {
                    return $orderA <=> $orderB;
                }
            }

            // Special ordering for bg-* classes
            if ('bg' === $prefixA) {
                $orderA = $this->getBgSubOrder($baseA);
                $orderB = $this->getBgSubOrder($baseB);
                if ($orderA !== $orderB) {
                    return $orderA <=> $orderB;
                }
            }

            // Special ordering for animate-* classes (alphabetical)
            if ('animate' === $prefixA) {
                return \strcmp($baseA, $baseB);
            }

            // Special ordering for divide-* classes (direction before style before color)
            if ('divide' === $prefixA) {
                $orderA = $this->getDivideSubOrder($baseA);
                $orderB = $this->getDivideSubOrder($baseB);
                if ($orderA !== $orderB) {
                    return $orderA <=> $orderB;
                }
            }
        }

        $numA = $this->extractNumericValue($a);
        $numB = $this->extractNumericValue($b);

        if (null !== $numA && null !== $numB) {
            return $numA <=> $numB;
        }

        return \strcmp($a, $b);
    }

    private function getTextSubOrder(string $class): int
    {
        // text-left, text-center, text-right (alignment) - Tailwind CSS order (left=0, center=1, right=2)
        // Multiplied by 0.001 in getClassOrder: 0, 0.001, 0.002 — all below size (0.01) and font (0.015)
        if (\preg_match('/^text-(left|center|right|justify|start|end)$/', $class, $matches)) {
            return ['left' => 0, 'center' => 1, 'right' => 2, 'justify' => 3, 'start' => 4, 'end' => 5][$matches[1]];
        }

        // text-xs, text-sm, text-base, text-lg, etc. (size) = 10 → 0.01
        if (\preg_match('/^text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl)$/', $class)) {
            return 10;
        }

        // font is at 0.015, leading is at 0.025 (set in buildOrderMap)

        // text-opacity-* = 30 → 0.03 (after font 0.015 and leading 0.025)
        if (\str_starts_with($class, 'text-opacity-')) {
            return 30;
        }

        // text-{color} (colors) = 40 → 0.04 (after opacity)
        return 40;
    }

    private function getBgSubOrder(string $class): int
    {
        // bg-{color} = 0 (first, per Prettier)
        if (\preg_match('/^bg-(slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|white|black|transparent|current|inherit)(-|$)/', $class)) {
            return 0;
        }

        // bg-opacity-* = 1
        if (\str_starts_with($class, 'bg-opacity-')) {
            return 1;
        }

        // bg-gradient, bg-cover, bg-contain, etc. = 2
        return 2;
    }

    private function getDivideSubOrder(string $class): int
    {
        // divide-x, divide-y (direction/width) should come first
        if (\preg_match('/^divide-[xy]$/', $class)) {
            return 0;
        }

        // divide-solid, divide-dashed, etc. (style) in middle
        if (\preg_match('/^divide-(solid|dashed|dotted|double|none)$/', $class)) {
            return 1;
        }

        // divide-{color} should come last
        return 2;
    }

    private function extractNumericValue(string $class): ?float
    {
        // Extract base class without variants
        $parts = \explode(':', $class);
        $baseClass = \end($parts);

        // Match numeric values like p-4, text-2xl, w-1/2
        if (\preg_match('/-(\d+(?:\.\d+)?)(?:\/(\d+))?$/', $baseClass, $matches)) {
            if (isset($matches[2])) {
                // Fraction like w-1/2 - use null so strcmp is used (matches Prettier's alphabetical sort)
                return null;
            }

            return (float) $matches[1];
        }

        return null;
    }

    private function buildOrderMap(): void
    {
        foreach (self::CLASS_ORDER as $index => $classPrefix) {
            $this->orderMap[$classPrefix] = $index;
        }

        // Special adjustments for typography ordering
        // Text sub-orders: alignment(0), size(1) → font(0.15) → leading(0.25) → opacity(3), color(4)
        $textOrder = $this->orderMap['text'];
        $this->orderMap['font'] = $textOrder + 0.015; // Between size (0.01) and leading (0.025)
        $this->orderMap['leading'] = $textOrder + 0.025; // After font
    }
}
