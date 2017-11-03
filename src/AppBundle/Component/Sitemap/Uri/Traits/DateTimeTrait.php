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

use SR\Exception\Logic\InvalidArgumentException;

trait DateTimeTrait
{
    /**
     * @param \DateTime|null $dateTime
     * @param bool           $allowFutureDates
     *
     * @return \DateTime|null
     */
    private function sanitizeDateTime(\DateTime $dateTime = null, bool $allowFutureDates = false): ? \DateTime
    {
        if (null !== $dateTime && !$allowFutureDates && $dateTime->format('U') > time()) {
            throw new InvalidArgumentException('Last modified "%s" cannot be in the future.', $dateTime->format('c'));
        }

        return $dateTime;
    }

    /**
     * See https://www.w3.org/TR/NOTE-datetime for information on the standard used for datetime formatting.
     *
     * @param \DateTime $dateTime
     * @param bool      $precision
     *
     * @return string
     */
    private function formatDateTimeAsW3C(\DateTime $dateTime = null, bool $precision = true) : string
    {
        $format = 'Y-m-d';

        if ($precision) {
            $format .= '\TH:i:sP';
        }

        return $dateTime ? $dateTime->format($format) : '';
    }
}
