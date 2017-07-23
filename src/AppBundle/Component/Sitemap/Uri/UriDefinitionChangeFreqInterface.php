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

interface UriDefinitionChangeFreqInterface
{
    /**
     * @var string
     */
    const CHANGE_FREQ_ALWAYS = 'always';

    /**
     * @var string
     */
    const CHANGE_FREQ_HOURLY = 'hourly';

    /**
     * @var string
     */
    const CHANGE_FREQ_DAILY = 'daily';

    /**
     * @var string
     */
    const CHANGE_FREQ_WEEKLY = 'weekly';

    /**
     * @var string
     */
    const CHANGE_FREQ_MONTHLY = 'monthly';

    /**
     * @var string
     */
    const CHANGE_FREQ_YEARLY = 'yearly';

    /**
     * @var string
     */
    const CHANGE_FREQ_NEVER = 'never';
}
