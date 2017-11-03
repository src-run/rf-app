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

use Rf\AppBundle\Component\Sitemap\Uri\Traits\DateTimeTrait;
use Rf\AppBundle\Component\Sitemap\Uri\Traits\DescriptionTrait;
use Rf\AppBundle\Component\Sitemap\Uri\Traits\LocationTrait;
use Rf\AppBundle\Component\Sitemap\Uri\Traits\XmlEscapingTrait;
use SR\Exception\Logic\InvalidArgumentException;
use SR\Exception\Logic\LogicException;

class UriDefinitionMediaVideo implements UriDefinitionMediaInterface
{
    use DateTimeTrait;
    use DescriptionTrait;
    use LocationTrait;
    use XmlEscapingTrait;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $thumbnail;

    /**
     * @var \DateTime|null
     */
    private $publicationDate;

    /**
     * @var \DateTime|null
     */
    private $expirationDate;

    /**
     * @var string|null
     */
    private $playerLocation;

    /**
     * @var bool
     */
    private $playerEmbeddable = true;

    /**
     * @var bool
     */
    private $playerAutoPlayable = false;

    /**
     * @var int|null
     */
    private $duration;

    /**
     * @var float|null
     */
    private $rating;

    /**
     * @var int|null
     */
    private $viewCount;

    /**
     * @var string[]
     */
    private $tags = [];

    /**
     * @var string|null
     */
    private $category;

    /**
     * @var string[]
     */
    private $countryRestrictions = [];

    /**
     * @var string
     */
    private $countryRestrictionsRelationship;

    /**
     * @var string[]
     */
    private $platformRestrictions = [];

    /**
     * @var string
     */
    private $platformRestrictionsRelationship;

    /**
     * @var bool
     */
    private $familyFriendly = true;

    /**
     * @var string|null
     */
    private $uploaderName;

    /**
     * @var string|null
     */
    private $uploaderLink;

    /**
     * @var bool
     */
    private $live = false;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $identifierContext;

    /**
     * @param string $location
     * @param string $thumbnail
     * @param string $title
     * @param string $description
     */
    public function __construct(string $location, string $thumbnail, string $title = '', string $description = '')
    {
        $this->setLocation($location);
        $this->setThumbnail($thumbnail);
        $this->setTitle($title);
        $this->setDescription($description);
    }

    /**
     * @param string $title
     *
     * @return self
     */
    public function setTitle(string $title): self
    {
        $title = $this->toHtmlSpecialChars($title);

        if (strlen($title) > 100) {
            throw new InvalidArgumentException('Title cannot exceed 100 characters (got %d).', strlen($title));
        }

        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $thumbnail
     *
     * @return self
     */
    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $this->sanitizeLocation($thumbnail);

        return $this;
    }

    /**
     * @return string
     */
    public function getThumbnail(): string
    {
        return $this->thumbnail;
    }

    /**
     * @param \DateTime|null $publicationDate
     *
     * @return self
     */
    public function setPublicationDate(\DateTime $publicationDate = null): self
    {
        $this->publicationDate = $this->sanitizeDateTime($publicationDate);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPublicationDate(): bool
    {
        return $this->publicationDate !== null;
    }

    /**
     * @return \DateTime|null
     */
    public function getPublicationDate(): ? \DateTime
    {
        return $this->publicationDate;
    }

    /**
     * @param \DateTime|null $expirationDate
     *
     * @return self
     */
    public function setExpirationDate(\DateTime $expirationDate = null) : self
    {
        $this->expirationDate = $this->sanitizeDateTime($expirationDate, true);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasExpirationDate(): bool
    {
        return $this->expirationDate !== null;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpirationDate(): ? \DateTime
    {
        return $this->expirationDate;
    }

    /**
     * @return string
     */
    public function getExpirationDateString() : string
    {
        return $this->formatDateTimeAsW3C($this->expirationDate);
    }

    /**
     * @return string
     */
    public function getPublicationDateString(): string
    {
        return $this->formatDateTimeAsW3C($this->publicationDate);
    }

    /**
     * @param string|null $playerLocation
     *
     * @return self
     */
    public function setPlayerLocation(string $playerLocation = null): self
    {
        $this->playerLocation = $this->sanitizeLocation($playerLocation);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPlayerLocation(): bool
    {
        return $this->playerLocation !== null;
    }

    /**
     * @return null|string
     */
    public function getPlayerLocation(): ? string
    {
        return $this->playerLocation;
    }

    /**
     * @param bool $playerEmbeddable
     *
     * @return self
     */
    public function setPlayerEmbeddable(bool $playerEmbeddable) : self
    {
        $this->playerEmbeddable = $playerEmbeddable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPlayerEmbeddable(): bool
    {
        return $this->playerEmbeddable;
    }

    /**
     * @param bool $playerAutoPlayable
     *
     * @return self
     */
    public function setPlayerAutoPlayable(bool $playerAutoPlayable): self
    {
        $this->playerAutoPlayable = $playerAutoPlayable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPlayerAutoPlayable(): bool
    {
        return $this->playerAutoPlayable;
    }

    /**
     * @param int|null $seconds
     *
     * @return self
     */
    public function setDuration(int $seconds = null): self
    {
        if (null !== $seconds && ($seconds <= 0 || $seconds > 28800)) {
            throw new InvalidArgumentException('Duration must be a non-zero, positive number under 28800 (got %d).', $seconds);
        }

        $this->duration = $seconds;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasDuration(): bool
    {
        return $this->duration !== null;
    }

    /**
     * @return int|null
     */
    public function getDuration(): ? int
    {
        return $this->duration;
    }

    /**
     * @param float|null $rating
     *
     * @return self
     */
    public function setRating(float $rating = null) : self
    {
        if (null !== $rating && ($rating > 5 || $rating < 0)) {
            throw new InvalidArgumentException('Rating must be between 0 and 5 (got %d).', $rating);
        }

        $this->rating = $rating;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasRating(): bool
    {
        return $this->rating !== null;
    }

    /**
     * @return float|null
     */
    public function getRating(): ? float
    {
        return $this->rating;
    }

    /**
     * @param int|null $viewCount
     *
     * @return self
     */
    public function setViewCount(int $viewCount = null) : self
    {
        if (null !== $viewCount && $viewCount < 0) {
            throw new InvalidArgumentException('View count must be a non-negative number (got %d).', $viewCount);
        }

        $this->viewCount = $viewCount;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasViewCount(): bool
    {
        return $this->viewCount !== null;
    }

    /**
     * @return int|null
     */
    public function getViewCount(): ? int
    {
        return $this->viewCount;
    }

    /**
     * @param string[] ...$tags
     *
     * @return self
     */
    public function setTags(string ...$tags) : self
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }

        return $this;
    }

    /**
     * @param string $tag
     *
     * @return self
     */
    public function addTag(string $tag): self
    {
        if (count($this->tags) >= 32) {
            throw new InvalidArgumentException('A maximum of 32 tags can be set per video.');
        }

        if (!is_string($tag)) {
            throw new InvalidArgumentException('Tag must be of type "string" (got %s).', gettype($tag));
        }

        $tag = $this->toHtmlSpecialChars($tag);

        if (!in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasTags(): bool
    {
        return count($this->tags) !== 0;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param string|null $category
     *
     * @return self
     */
    public function setCategory(string $category = null): self
    {
        $category = $this->toHtmlSpecialChars($category);

        if (strlen($category) > 256) {
            throw new InvalidArgumentException('Category cannot be more than 256 characters long (got %d).', strlen($category));
        }

        $this->category = $category;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCategory(): bool
    {
        return $this->category !== null;
    }

    /**
     * @return null|string
     */
    public function getCategory(): ? string
    {
        return $this->category;
    }

    /**
     * @param string $relationship
     * @param string[] ...$platforms
     *
     * @return self
     */
    public function setPlatformRestrictions(string $relationship, string ...$platforms) : self
    {
        $this->platformRestrictionsRelationship = $this->sanitizeRelationship($relationship);
        $this->addPlatformRestrictions(...$platforms);

        return $this;
    }

    /**
     * @param string[] ...$platforms
     *
     * @return self
     */
    public function addPlatformRestrictions(string ...$platforms): self
    {
        if (null === $this->countryRestrictionsRelationship) {
            throw new LogicException('Cannot add platform restrictions without first setting the "relationship": use "setPlatformRegistrations()" before calling "%s()".', __METHOD__);
        }

        $p = array_map(function ($platform) {
            return strtolower((string) $platform);
        }, $platforms);

        foreach ($p as $platform) {
            if (!in_array($platform, ['web', 'mobile', 'tv'])) {
                throw new InvalidArgumentException('Platform must be one of "web", "mobile", or "tv" (got %s).', $p);
            }
        }

        $this->platformRestrictions = $p;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPlatformRestrictions(): bool
    {
        return count($this->platformRestrictions) !== 0;
    }

    /**
     * @return \Generator
     */
    public function getPlatformRestrictions(): \Generator
    {
        foreach ($this->platformRestrictions as $platform) {
            yield $this->platformRestrictionsRelationship => $platform;
        }
    }

    /**
     * @return null|string
     */
    public function getPlatformRestrictionsRelationship(): ? string
    {
        return $this->platformRestrictionsRelationship;
    }

    /**
     * @param string $relationship
     * @param string[] ...$countries
     *
     * @return self
     */
    public function setCountryRestrictions(string $relationship, string ...$countries) : self
    {
        $this->countryRestrictionsRelationship = $this->sanitizeRelationship($relationship);
        $this->addCountryRestrictions(...$countries);

        return $this;
    }

    /**
     * @param string[] ...$countries
     *
     * @return self
     */
    public function addCountryRestrictions(string ...$countries): self
    {
        if (null === $this->countryRestrictionsRelationship) {
            throw new LogicException('Cannot add country restrictions without first setting the "relationship": use "setCountryRegistrations()" before calling "%s()".', __METHOD__);
        }

        $c = array_map(function ($country) {
            return strtoupper((string) $country);
        }, $countries);

        foreach ($c as $country) {
            if (strlen($country) > 3 || strlen($country) < 2) {
                throw new InvalidArgumentException('Country must be a two or three character country code (got %s).', $c);
            }
        }

        $this->countryRestrictions = $c;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCountryRestrictions(): bool
    {
        return count($this->countryRestrictions) !== 0;
    }

    /**
     * @return \Generator
     */
    public function getCountryRestrictions(): \Generator
    {
        foreach ($this->countryRestrictions as $country) {
            yield $this->countryRestrictionsRelationship => $country;
        }
    }

    /**
     * @return null|string
     */
    public function getCountryRestrictionsRelationship(): ? string
    {
        return $this->countryRestrictionsRelationship;
    }

    /**
     * @param bool $familyFriendly
     *
     * @return self
     */
    public function setFamilyFriendly(bool $familyFriendly) : self
    {
        $this->familyFriendly = $familyFriendly;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFamilyFriendly(): bool
    {
        return $this->familyFriendly;
    }

    /**
     * @param string|null $name
     * @param string|null $link
     *
     * @return self
     */
    public function setUploader(string $name = null, string $link = null): self
    {
        $this->setUploaderName($name);
        $this->setUploaderLink($link);

        return $this;
    }

    /**
     * @param string|null $uploaderName
     *
     * @return self
     */
    public function setUploaderName(string $uploaderName = null): self
    {
        $this->uploaderName = $this->toHtmlSpecialChars($uploaderName);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasUploaderName(): bool
    {
        return $this->uploaderName !== null;
    }

    /**
     * @return null|string
     */
    public function getUploaderName(): ? string
    {
        return $this->uploaderName;
    }

    /**
     * @param string|null $uploaderLink
     *
     * @return self
     */
    public function setUploaderLink(string $uploaderLink = null) : self
    {
        $uploaderLink = $this->sanitizeLocation($uploaderLink);

        if (($uploaderHost = parse_url($uploaderLink, PHP_URL_HOST)) !== ($locationHost = parse_url($this->location, PHP_URL_HOST))) {
            throw new InvalidArgumentException('Uploader link must match the domain of the video itself (got "%s" but expected "%s").', $uploaderHost, $locationHost);
        }

        $this->uploaderLink = $uploaderLink;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasUploaderLink(): bool
    {
        return $this->uploaderLink !== null;
    }

    /**
     * @return null|string
     */
    public function getUploaderLink(): ? string
    {
        return $this->uploaderLink;
    }

    /**
     * @param bool $live
     *
     * @return self
     */
    public function setLive(bool $live) : self
    {
        $this->live = $live;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLive(): bool
    {
        return $this->live;
    }

    /**
     * @param string $relationship
     *
     * @return string
     */
    private function sanitizeRelationship(string $relationship): string
    {
        $relationship = strtolower($relationship);

        if (!in_array($relationship, ['allow', 'deny'])) {
            throw new InvalidArgumentException('Platform relationship must be one of "allow" or "deny" (got %s).', $relationship);
        }

        return $relationship;
    }

    /**
     * @param string $identifier
     * @param string $context
     *
     * @return self
     */
    public function setIdentifier(string $identifier, string $context): self
    {
        $context = strtolower($context);
        if (!in_array($context, ['tms:series', 'tms:program', 'rovi:series', 'rovi:program', 'freebase', 'url'])) {
            throw new InvalidArgumentException('Identifier context must be one of "tms:series", "tms:program", "rovi:series", "rovi:program", "freebase", or "url" (got "%s").', $context);
        }

        $this->identifier = $identifier;
        $this->identifierContext = $context;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasIdentifier(): bool
    {
        return $this->identifier !== null && $this->identifierContext !== null;
    }

    /**
     * @return null|string
     */
    public function getIdentifier(): ? string
    {
        return $this->identifier;
    }

    /**
     * @return null|string
     */
    public function getIdentifierContext() : ? string
    {
        return $this->identifierContext;
    }

    /**
     * @return string
     */
    public static function getType() : string
    {
        return UriDefinitionMediaInterface::MEDIA_TYPE_IMAGE;
    }
}
