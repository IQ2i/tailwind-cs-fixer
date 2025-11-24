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

namespace TailwindCsFixer\Console;

use Symfony\Component\Console\Application as BaseApplication;
use TailwindCsFixer\Console\Command\FixCommand;

class Application extends BaseApplication
{
    private const VERSION = '1.0.0';
    private const NAME = 'Tailwind-CS-Fixer';

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);

        $this->add(new FixCommand());
        $this->setDefaultCommand('fix', true);
    }

    public function getLongVersion(): string
    {
        return \sprintf(
            '<info>%s</info> version <comment>%s</comment>',
            $this->getName(),
            $this->getVersion()
        );
    }
}
