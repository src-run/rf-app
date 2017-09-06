<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\DataFixtures\ORM;

use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

trait DataFixturesFakerTrait
{
    /**
     * @return Generator
     */
    private function getGenerator(): Generator
    {
        static $generator = null;

        if ($generator === null) {
            $generator = Factory::create();
        }

        return $generator;
    }

    /**
     * @param array $lines
     * @param int   $count
     * @param int   $multiplier
     */
    private function fakeSentences(array &$lines, $count, $multiplier = 1)
    {
        foreach (range(0, $count) as $i) {
            $lines[] = $this->getGenerator()->sentence(mt_rand(20 * $multiplier, 100 * $multiplier));
            $this->addEmptyLine($lines);
        }
    }

    /**
     * @param array $lines
     * @param int   $count
     * @param int   $multiplier
     */
    private function fakeUnorderedList(array &$lines, $count, $multiplier = 1)
    {
        foreach (range(1, $count) as $i) {
            $lines[] = sprintf('- %s', $this->getGenerator()->sentence(5 * $multiplier, 15 * $multiplier));
        }

        $this->addEmptyLine($lines);
    }

    /**
     * @param array $lines
     * @param int   $count
     * @param int   $multiplier
     */
    private function fakeOrderedList(array &$lines, $count, $multiplier = 1)
    {
        foreach (range(1, $count) as $i) {
            $lines[] = sprintf('%d. %s', $count, $this->getGenerator()->sentence(8 * $multiplier, 24 * $multiplier));
        }

        $this->addEmptyLine($lines);
    }

    /**
     * @param array $lines
     * @param int   $level
     */
    private function fakeHeader(array &$lines, $level = 1)
    {
        $this->addEmptyLine($lines);
        $lines[] = vsprintf('%s %s', [
            str_repeat('#', $level),
            ucwords(trim($this->getGenerator()->sentence(mt_rand(4, 20)), '.')),
        ]);
        $this->addEmptyLine($lines);
    }

    /**
     * @param array $lines
     */
    private function fakeCodeBlock(array &$lines)
    {
        $this->addEmptyLine($lines);

        $finder = Finder::create()
            ->in(__DIR__.'/../../')
            ->name('*.php')
            ->files();

        $collection = [];
        foreach ($finder as $f) {
            $collection[] = $f;
        }

        /** @var SplFileInfo $file */
        $file = $collection[mt_rand(0, count($collection) - 1)];

        $lines = array_merge($lines, [
            '```php',
            sprintf('# %s', $file->getRealPath()),
            '',
        ], explode(PHP_EOL, file_get_contents($file->getRealPath())), [
            '````',
        ]);

        $this->addEmptyLine($lines);
    }

    /**
     * @param array $lines
     */
    private function addEmptyLine(array &$lines)
    {
        $lines[] = '';
    }
}
