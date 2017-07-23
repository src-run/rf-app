<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Tests\Templating;

use PHPUnit\Framework\TestCase;
use Rf\AppBundle\Templating\IconExtension;

class IconExtensionTest extends TestCase
{
    /**
     * @return \Generator
     */
    public static function provideIconExtensionData(): \Generator
    {
        yield ['ion', 'star', false, [], '<i class="ion icon-ion ion-star"></i>'];
        yield ['ion', 'star', false, ['more', 'classes'], '<i class="ion icon-ion ion-star more classes"></i>'];
        yield ['ion', 'star', true, [], '<span class="icon-wrap-type-ion icon-wrap-name-ion-star"><i class="ion icon-ion ion-star"></i></span>'];
        yield ['fa', 'star', false, [], '<i class="fa icon-fa fa-star"></i>'];
        yield ['fa', 'star', false, ['more', 'classes'], '<i class="fa icon-fa fa-star more classes"></i>'];
        yield ['fa', 'star', true, [], '<span class="icon-wrap-type-fa icon-wrap-name-fa-star"><i class="fa icon-fa fa-star"></i></span>'];
    }

    /**
     * @dataProvider provideIconExtensionData
     *
     * @param string $type
     * @param string $name
     * @param bool   $useWrapper
     * @param array  $useClasses
     * @param string $expected
     */
    public function testIconExtension(string $type, string $name, bool $useWrapper, array $useClasses, string $expected)
    {
        $e = $this->getIconExtensionInstance();
        $f = $e->getFunctions()[$type === 'ion' ? 0 : 1];
        $c = $f->getCallable();

        $this->assertSame($expected, $c($name, $useWrapper, $useClasses));
    }

    /**
     * @return IconExtension
     */
    private function getIconExtensionInstance(): IconExtension
    {
        return new IconExtension();
    }
}
