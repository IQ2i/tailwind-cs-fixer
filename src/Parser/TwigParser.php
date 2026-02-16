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
        $content = $this->parseStaticClasses($content);
        $content = $this->parseMixedClasses($content);

        return $content;
    }

    private function parseStaticClasses(string $content): string
    {
        $pattern = '/class=(["|\'])(?!.*\{\{)([^"\']*)(\1)/';

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
            if (!\str_contains($fullContent, '{{')) {
                return $matches[0];
            }

            $parts = $this->extractTwigExpressions($fullContent);

            $result = [];
            foreach ($parts as $part) {
                if ($part['type'] === 'twig') {
                    $result[] = $part['content'];
                } else {
                    $trimmed = \trim($part['content']);
                    if (!empty($trimmed)) {
                        $sorted = $this->sorter->sort($trimmed);
                        $result[] = $sorted;
                    }
                }
            }

            return 'class='.$quote.\implode(' ', \array_filter($result)).$quote;
        }, $content);
    }

    private function extractTwigExpressions(string $content): array
    {
        $parts = [];
        $length = \strlen($content);
        $i = 0;
        $current = '';

        while ($i < $length) {
            // Check if we're starting a Twig expression
            if ($i < $length - 1 && $content[$i] === '{' && $content[$i + 1] === '{') {
                // Save any accumulated non-Twig content
                if ($current !== '') {
                    $parts[] = ['type' => 'static', 'content' => $current];
                    $current = '';
                }

                // Extract the full Twig expression with balanced braces
                $twigExpr = '{{';
                $i += 2;
                $braceDepth = 1;

                while ($i < $length && $braceDepth > 0) {
                    $char = $content[$i];

                    if ($i < $length - 1 && $char === '{' && $content[$i + 1] === '{') {
                        $twigExpr .= '{{';
                        $i += 2;
                        ++$braceDepth;
                    } elseif ($i < $length - 1 && $char === '}' && $content[$i + 1] === '}') {
                        $twigExpr .= '}}';
                        $i += 2;
                        --$braceDepth;
                    } else {
                        $twigExpr .= $char;
                        ++$i;
                    }
                }

                $parts[] = ['type' => 'twig', 'content' => $twigExpr];
            } else {
                $current .= $content[$i];
                ++$i;
            }
        }

        // Save any remaining content
        if ($current !== '') {
            $parts[] = ['type' => 'static', 'content' => $current];
        }

        return $parts;
    }
}
