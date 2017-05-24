<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Templating;

use Rf\AppBundle\Component\Environment\EnvironmentInterface;
use SR\Cocoa\Transformer\CacheableTransformerInterface;
use SR\Cocoa\Transformer\TransformerInterface;

class MarkdownExtension extends \Twig_Extension
{
    /**
     * @var CacheableTransformerInterface
     */
    private $transformer;

    /**
     * @param TransformerInterface $transformer
     * @param EnvironmentInterface $environment
     */
    public function __construct(TransformerInterface $transformer, EnvironmentInterface $environment)
    {
        if ($transformer instanceof CacheableTransformerInterface) {
            $this->transformer = $transformer->setExpiresAfter(
                new \DateInterval(!$environment->isDevelopment() ? 'PT0S' : 'PT12H')
            );
        }
    }

    /**
     * @return \Twig_Function[]
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_Function('markdown', function (array $strings = []) {
                return $this->getTransformations(...$strings);
            }, [
                'is_safe' => ['html'],
                'is_variadic' => true,
            ]),
        ];
    }

    /**
     * @param array ...$strings
     *
     * @return string
     */
    private function getTransformations(...$strings)
    {
        return implode(PHP_EOL, array_map(function (string $string) {
            return $this->transformer->transform($string);
        }, $strings));
    }
}
