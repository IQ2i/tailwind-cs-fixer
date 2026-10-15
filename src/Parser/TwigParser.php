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

namespace TailwindCsFixer\Parser;

use TailwindCsFixer\Sorter\TailwindClassSorter;

class TwigParser
{
    private TailwindClassSorter $sorter;

    public function __construct(TailwindClassSorter $sorter)
    {
        $this->sorter = $sorter;
    }

    public function parse(string $content): string
    {
        $comments = [];
        $content = \preg_replace_callback('/\{#.*?#\}|<!--.*?-->/s', function ($matches) use (&$comments) {
            $placeholder = "\x00C".\count($comments)."\x00";
            $comments[$placeholder] = $matches[0];

            return $placeholder;
        }, $content);

        $content = $this->parseStaticClasses($content);
        $content = $this->parseMixedClasses($content);

        return \str_replace(\array_keys($comments), \array_values($comments), $content);
    }

    private function parseStaticClasses(string $content): string
    {
        $pattern = '/class=(["|\'])(?![^"\']*(?:\{\{|\{%))([^"\']*)(\1)/';

        return \preg_replace_callback($pattern, function ($matches) {
            $quote = $matches[1];
            $classes = $matches[2];
            $sortedClasses = $this->sorter->sort($classes);

            return 'class='.$quote.$sortedClasses.$quote;
        }, $content);
    }

    private function parseMixedClasses(string $content): string
    {
        return \preg_replace_callback('/class=(["|\'])(.+?)\1/s', function ($matches) {
            $quote = $matches[1];
            $fullContent = $matches[2];

            // If no Twig expressions, skip
            if (!\str_contains($fullContent, '{{') && !\str_contains($fullContent, '{%')) {
                return $matches[0];
            }

            $parts = $this->extractTwigExpressions($fullContent);
            $lastIndex = \count($parts) - 1;

            $result = '';
            foreach ($parts as $index => $part) {
                // {% %} block tags (e.g. {% if %}) delimit a conditionally
                // rendered class, so they always need surrounding whitespace
                // to avoid merging into neighbouring classes at runtime.
                if ('block' === $part['type']) {
                    $result = \rtrim($result);
                    if ('' !== $result) {
                        $result .= ' ';
                    }
                    $result .= $part['content'].' ';

                    continue;
                }

                // {{ }} variable expressions are appended as-is: they may be
                // glued to a static prefix/suffix on purpose (e.g. a BEM
                // modifier like `panel--{{ metric }}`), so we must not
                // invent whitespace that wasn't in the original markup.
                if ('expr' === $part['type']) {
                    $result .= $part['content'];

                    continue;
                }

                $hasLeadingSpace = '' !== $part['content'] && \ctype_space($part['content'][0]);
                $hasTrailingSpace = '' !== $part['content'] && \ctype_space(\substr($part['content'], -1));
                $trimmed = \trim($part['content']);

                if ('' === $trimmed) {
                    $rtrimmed = \rtrim($result);
                    if ('' !== $rtrimmed && ($hasLeadingSpace || $hasTrailingSpace)) {
                        $result = $rtrimmed.' ';
                    }

                    continue;
                }

                // A token glued to a neighbouring Twig expression (no space
                // in between) must keep its position after sorting, otherwise
                // sorting could move it away from the expression it's glued to.
                $tokens = \preg_split('/\s+/', $trimmed);
                $gluedFirstToken = 0 !== $index && !$hasLeadingSpace ? \array_shift($tokens) : null;
                $gluedLastToken = $lastIndex !== $index && !$hasTrailingSpace && [] !== $tokens ? \array_pop($tokens) : null;

                $sortedMiddle = [] !== $tokens ? $this->sorter->sort(\implode(' ', $tokens)) : '';
                $sorted = \implode(' ', \array_filter([$gluedFirstToken, $sortedMiddle, $gluedLastToken], static fn ($token) => null !== $token && '' !== $token));

                if ($hasLeadingSpace && '' !== $result && ' ' !== \substr($result, -1)) {
                    $result .= ' ';
                }

                $result .= $sorted;

                if ($hasTrailingSpace) {
                    $result .= ' ';
                }
            }

            return 'class='.$quote.\trim($result).$quote;
        }, $content);
    }

    private function extractTwigExpressions(string $content): array
    {
        $parts = [];
        $length = \strlen($content);
        $i = 0;
        $current = '';

        while ($i < $length) {
            // Check if we're starting a Twig variable expression {{ ... }}
            if ($i < $length - 1 && '{' === $content[$i] && '{' === $content[$i + 1]) {
                // Save any accumulated non-Twig content
                if ('' !== $current) {
                    $parts[] = ['type' => 'static', 'content' => $current];
                    $current = '';
                }

                // Extract the full Twig expression with balanced braces
                $twigExpr = '{{';
                $i += 2;
                $braceDepth = 1;

                while ($i < $length && $braceDepth > 0) {
                    $char = $content[$i];

                    if ($i < $length - 1 && '{' === $char && '{' === $content[$i + 1]) {
                        $twigExpr .= '{{';
                        $i += 2;
                        ++$braceDepth;
                    } elseif ($i < $length - 1 && '}' === $char && '}' === $content[$i + 1]) {
                        $twigExpr .= '}}';
                        $i += 2;
                        --$braceDepth;
                    } else {
                        $twigExpr .= $char;
                        ++$i;
                    }
                }

                $parts[] = ['type' => 'expr', 'content' => $twigExpr];
            } elseif ($i < $length - 1 && '{' === $content[$i] && '%' === $content[$i + 1]) {
                // Check if we're starting a Twig block tag {% ... %}
                if ('' !== $current) {
                    $parts[] = ['type' => 'static', 'content' => $current];
                    $current = '';
                }

                // Extract until closing %}
                $twigBlock = '{%';
                $i += 2;

                while ($i < $length) {
                    if ($i < $length - 1 && '%' === $content[$i] && '}' === $content[$i + 1]) {
                        $twigBlock .= '%}';
                        $i += 2;
                        break;
                    }
                    $twigBlock .= $content[$i];
                    ++$i;
                }

                $parts[] = ['type' => 'block', 'content' => $twigBlock];
            } else {
                $current .= $content[$i];
                ++$i;
            }
        }

        // Save any remaining content
        if ('' !== $current) {
            $parts[] = ['type' => 'static', 'content' => $current];
        }

        return $parts;
    }
}
