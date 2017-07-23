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

use Rf\AppBundle\Component\Registry\Metadata\MetadataRegistry;

class MetadataExtension extends \Twig_Extension
{
    /**
     * @var MetadataRegistry
     */
    private $metadataRegistry;

    /**
     * @param MetadataRegistry $metadataRegistry
     */
    public function __construct(MetadataRegistry $metadataRegistry)
    {
        $this->metadataRegistry = $metadataRegistry;
    }

    /**
     * @return \Twig_Function[]
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_Function('meta_registry', function () {
                return $this->metadataRegistry;
            }),
        ];
    }
}
