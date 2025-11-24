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
        $pattern = '/class=(["|\'])([^"\']*\{\{[^"\']*)(["|\'])/';

        return \preg_replace_callback($pattern, function ($matches) {
            $quote = $matches[1];
            $fullContent = $matches[2];

            $parts = \preg_split('/(\{\{[^}]+\}\})/', $fullContent, -1, \PREG_SPLIT_DELIM_CAPTURE);

            $result = [];
            foreach ($parts as $part) {
                if (\preg_match('/^\{\{.*\}\}$/', $part)) {
                    $result[] = $part;
                } else {
                    $trimmed = \trim($part);
                    if (!empty($trimmed)) {
                        $sorted = $this->sorter->sort($trimmed);
                        $result[] = $sorted;
                    }
                }
            }

            return 'class='.$quote.\implode(' ', \array_filter($result)).$quote;
        }, $content);
    }
}
