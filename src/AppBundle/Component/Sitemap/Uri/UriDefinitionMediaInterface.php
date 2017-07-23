<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Sitemap\Uri;

interface UriDefinitionMediaInterface
{
    /**
     * @var string
     */
    const MEDIA_TYPE_VIDEO = 'video';

    /**
     * @var string
     */
    const MEDIA_TYPE_IMAGE = 'image';

    /**
     * @return string
     */
    static public function getType(): string;
}
