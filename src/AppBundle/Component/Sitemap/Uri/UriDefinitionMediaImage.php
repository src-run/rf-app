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

use Rf\AppBundle\Component\Sitemap\Uri\Traits\DescriptionTrait;
use Rf\AppBundle\Component\Sitemap\Uri\Traits\LocationTrait;
use Rf\AppBundle\Component\Sitemap\Uri\Traits\XmlEscapingTrait;

class UriDefinitionMediaImage implements UriDefinitionMediaInterface
{
    use DescriptionTrait;
    use LocationTrait;
    use XmlEscapingTrait;

    /**
     * @var string|null
     */
    private $geoLocation;

    /**
     * @var string|null
     */
    private $license;

    /**
     * @param string      $location
     * @param string|null $description
     * @param string|null $geoLocation
     */
    public function __construct(string $location, string $description = null, string $geoLocation = null)
    {
        $this->setLocation($location);
        $this->setDescription($description);
        $this->setGeoLocation($geoLocation);
    }

    /**
     * @param string|null $geoLocation
     *
     * @return self
     */
    public function setGeoLocation(string $geoLocation = null): self
    {
        $this->geoLocation = $this->toHtmlSpecialChars($geoLocation);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasGeoLocation(): bool
    {
        return $this->geoLocation !== null;
    }

    /**
     * @return null|string
     */
    public function getGeoLocation(): ? string
    {
        return $this->geoLocation;
    }

    /**
     * @param string|null $license
     *
     * @return self
     */
    public function setLicense(string $license = null) : self
    {
        $this->license = $this->toHtmlSpecialChars($license);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLicense(): bool
    {
        return $this->license !== null;
    }

    /**
     * @return null|string
     */
    public function getLicense(): ? string
    {
        return $this->license;
    }

    /**
     * @return string
     */
    public static function getType() : string
    {
        return UriDefinitionMediaInterface::MEDIA_TYPE_IMAGE;
    }
}
