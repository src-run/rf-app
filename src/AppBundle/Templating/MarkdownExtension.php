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

use Rf\AppBundle\Component\Environment\EnvironmentInterface;
use SR\Cocoa\Transformer\CacheableTransformerInterface;
use SR\Cocoa\Transformer\TransformerInterface;
use Symfony\Component\VarDumper\VarDumper;

class MarkdownExtension extends \Twig_Extension
{
    /**
     * @var array
     */
    static private $environmentOptions = [
        'is_safe' => ['html'],
    ];

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
            new \Twig_Function('markdown', function (string $string, string $type = 'markdown') {
                VarDumper::dump($type);
                VarDumper::dump($this->transformer->supports($type));
                return $this->transformer->transform($string);
            }, static::$environmentOptions),
        ];
    }
}
