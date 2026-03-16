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

class HtmlParser
{
    private TailwindClassSorter $sorter;

    public function __construct(TailwindClassSorter $sorter)
    {
        $this->sorter = $sorter;
    }

    public function parse(string $content): string
    {
        $comments = [];
        $content = \preg_replace_callback('/<!--.*?-->/s', function ($matches) use (&$comments) {
            $placeholder = "\x00C".\count($comments)."\x00";
            $comments[$placeholder] = $matches[0];

            return $placeholder;
        }, $content);

        $patterns = [
            '/class="([^"]+)"/',
            "/class='([^']+)'/",
        ];

        foreach ($patterns as $pattern) {
            $content = \preg_replace_callback($pattern, function ($matches) {
                $classes = $matches[1];
                $sortedClasses = $this->sorter->sort($classes);
                $quote = $matches[0][6];

                return 'class='.$quote.$sortedClasses.$quote;
            }, $content);
        }

        return \str_replace(\array_keys($comments), \array_values($comments), $content);
    }
}
