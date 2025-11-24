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
    private const CLASS_ORDER = [
        'container',
        'box-border', 'box-content',
        'block', 'inline-block', 'inline', 'flex', 'inline-flex', 'grid', 'inline-grid', 'flow-root', 'hidden',
        'static', 'fixed', 'absolute', 'relative', 'sticky',
        'inset', 'inset-x', 'inset-y', 'top', 'right', 'bottom', 'left',
        'visible', 'invisible', 'collapse',
        'flex-row', 'flex-row-reverse', 'flex-col', 'flex-col-reverse',
        'flex-wrap', 'flex-wrap-reverse', 'flex-nowrap',
        'place-content', 'place-items', 'place-self',
        'items-start', 'items-end', 'items-center', 'items-baseline', 'items-stretch',
        'justify-start', 'justify-end', 'justify-center', 'justify-between', 'justify-around', 'justify-evenly',
        'gap', 'gap-x', 'gap-y',
        'w', 'min-w', 'max-w',
        'h', 'min-h', 'max-h',
        'bg',
        'p', 'px', 'py', 'pt', 'pr', 'pb', 'pl',
        'm', 'mx', 'my', 'mt', 'mr', 'mb', 'ml',
        'space-x', 'space-y',
        'font', 'text',
        'leading', 'tracking',
        'uppercase', 'lowercase', 'capitalize', 'normal-case',
        'italic', 'not-italic',
        'underline', 'line-through', 'no-underline',
        'border', 'border-t', 'border-r', 'border-b', 'border-l', 'border-x', 'border-y',
        'rounded', 'rounded-t', 'rounded-r', 'rounded-b', 'rounded-l',
        'shadow', 'opacity',
        'transition', 'duration', 'ease', 'delay',
        'transform', 'scale', 'rotate', 'translate', 'skew',
        'cursor', 'pointer-events',
    ];

    private array $orderMap = [];

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
        $orderA = $this->getClassOrder($a);
        $orderB = $this->getClassOrder($b);

        $diff = $orderA - $orderB;
        if (\abs($diff) > 0.0001) {
            return $diff > 0 ? 1 : -1;
        }

        return $this->compareNumericValues($a, $b);
    }

    private function getClassOrder(string $class): float
    {
        $parts = \explode(':', $class);
        $baseClass = \end($parts);
        $variants = \array_slice($parts, 0, -1);

        if (isset($this->orderMap[$baseClass])) {
            $order = $this->orderMap[$baseClass];
            $prefix = $baseClass;
        } else {
            $prefix = $this->extractPrefix($baseClass);

            if (isset($this->orderMap[$prefix])) {
                $order = $this->orderMap[$prefix];
            } else {
                $order = 999999;
                $prefix = '';
            }
        }

        $variantOffset = $this->getVariantOffset($variants, $prefix);

        return $order + $variantOffset;
    }

    private function extractPrefix(string $class): string
    {
        if (\str_contains($class, '[')) {
            return \substr($class, 0, \strpos($class, '-'));
        }

        if (!\str_contains($class, '-')) {
            return $class;
        }

        $parts = \explode('-', $class);

        if (\count($parts) >= 2) {
            $firstPart = $parts[0];
            $secondPart = $parts[1] ?? '';

            if (\in_array($secondPart, ['t', 'r', 'b', 'l', 'x', 'y', 'tl', 'tr', 'bl', 'br'])) {
                return $firstPart.'-'.$secondPart;
            }

            return $firstPart;
        }

        return $class;
    }

    private function compareNumericValues(string $a, string $b): int
    {
        $numA = $this->extractNumericValue($a);
        $numB = $this->extractNumericValue($b);

        if (null !== $numA && null !== $numB) {
            return $numA <=> $numB;
        }

        return \strcmp($a, $b);
    }

    private function extractNumericValue(string $class): ?float
    {
        $parts = \explode(':', $class);
        $baseClass = \end($parts);

        if (\preg_match('/-(\d+(?:\.\d+)?)$/', $baseClass, $matches)) {
            return (float) $matches[1];
        }

        if (\preg_match('/-(\d+)\/(\d+)$/', $baseClass, $matches)) {
            return (float) $matches[1] / (float) $matches[2];
        }

        return null;
    }

    private function getVariantOffset(array $variants, string $prefix): float
    {
        if (empty($variants)) {
            return 0;
        }

        $stateVariants = ['hover', 'focus', 'active', 'disabled', 'visited', 'checked', 'group-hover', 'focus-within'];

        $hasStateVariant = false;
        foreach ($variants as $variant) {
            if (\in_array($variant, $stateVariants)) {
                $hasStateVariant = true;
                break;
            }
        }

        if ($hasStateVariant) {
            $effectsClasses = ['shadow', 'opacity'];
            if (\in_array($prefix, $effectsClasses)) {
                return 10;
            }

            return 0.001;
        }

        $offset = 0;
        foreach ($variants as $i => $variant) {
            $offset += ($i + 1) * 0.001;
        }

        return $offset;
    }

    private function buildOrderMap(): void
    {
        foreach (self::CLASS_ORDER as $index => $classPrefix) {
            $this->orderMap[$classPrefix] = $index;
        }
    }
}
