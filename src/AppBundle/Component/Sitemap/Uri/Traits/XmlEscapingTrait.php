<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Sitemap\Uri\Traits;

trait XmlEscapingTrait
{
    /**
     * @var int
     */
    private static $htmlEncodingFlags = ENT_COMPAT | ENT_QUOTES | ENT_DISALLOWED | ENT_XML1;

    /**
     * @var string
     */
    private static $htmlEncodingCharset = 'UTF-8';

    /**
     * @param string|null $string
     *
     * @return null|string
     */
    private function toHtmlEntities(string $string = null): ?string
    {
        return $string ? htmlentities($string, static::$htmlEncodingFlags, static::$htmlEncodingCharset) : $string;
    }

    /**
     * @param string|null $string
     *
     * @return null|string
     */
    private function toHtmlSpecialChars(string $string = null): ?string
    {
        return $string ? htmlspecialchars($string, static::$htmlEncodingFlags, static::$htmlEncodingCharset) : $string;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function toEncodedUrl(string $string): string
    {
        return urlencode($string);
    }
}
