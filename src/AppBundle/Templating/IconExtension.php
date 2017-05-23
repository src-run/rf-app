<?php

/*
 * This file is part of the `src-run/srw-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Templating;

class IconExtension extends \Twig_Extension
{
    /**
     * @var string
     */
    private static $templateWrap = '<span class="icon-wrap-type-%s icon-wrap-name-%s">%s</span>';

    /**
     * @var string
     */
    private static $templateIcon = '<i class="%s"></i>';

    /**
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_Function('ion', function (string $icon, $useWrapper = false, array $useClasses = []) {
                return $this->render('ion', $icon, $useWrapper, $useClasses);
            }, ['is_safe' => ['html']]),
            new \Twig_Function('fa', function (string $icon, $useWrapper = false, array $useClasses = []) {
                return $this->render('fa', $icon, $useWrapper, $useClasses);
            }, ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $type
     * @param string $icon
     * @param bool   $wrapped
     * @param array  $classes
     *
     * @return string
     */
    private function render(string $type, string $icon, $wrapped = false, array $classes = []): string
    {
        if (empty(trim($icon))) {
            return '';
        }

        $name = $type.'-'.$icon;
        $html = vsprintf(static::$templateIcon, implode(' ', array_merge([$type, 'icon-'.$type, $name], $classes)));

        return $wrapped ? sprintf(static::$templateWrap, $type, $name, $html) : $html;
    }
}
