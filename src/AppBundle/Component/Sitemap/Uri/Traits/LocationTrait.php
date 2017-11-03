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

trait LocationTrait
{
    /**
     * @var string
     */
    private $location;

    /**
     * @param string $location
     *
     * @return self
     */
    public function setLocation(string $location): self
    {
        $this->location = $this->sanitizeLocation($location);

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param null|string $location
     * @param bool        $throwError
     *
     * @return string
     */
    protected function sanitizeLocation(string $location = null, bool $throwError = true): ? string
    {
        if ($location) {
            try {
                if (!($url = parse_url($location))) {
                    throw new InvalidArgumentException('Location "%s" is malformed and could not be parsed.', $location);
                }

                if (!isset($url['scheme']) || empty($url['scheme'])) {
                    throw new InvalidArgumentException('Location "%s" must include a schema.', $location);
                }

                $location = $this->toHtmlEntities($location);

                if (strlen($location) > 2048) {
                    throw new InvalidArgumentException('Location "%s" cannot exceed 2048 characters (provided one is "%d" characters).', $location, strlen($location));
                }
            } catch (InvalidArgumentException $invalidArgumentException) {
                if (!$throwError) {
                    return '';
                }

                throw $invalidArgumentException;
            }
        }

        return $location;
    }
}
