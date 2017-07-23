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
use Rf\AppBundle\Component\Sitemap\Uri\Traits\LocationTrait;
use Rf\AppBundle\Component\Sitemap\Uri\Traits\XmlEscapingTrait;
use SR\Exception\Logic\InvalidArgumentException;

class UriDefinition implements UriDefinitionInterface, UriDefinitionChangeFreqInterface
{
    use DateTimeTrait;
    use LocationTrait;
    use XmlEscapingTrait;

    /**
     * @var \DateTime|null
     */
    private $lastModified;

    /**
     * @var bool
     */
    private $lastModifiedPrecise = false;

    /**
     * @var string|null
     */
    private $changeFrequency;

    /**
     * @var float
     */
    private $priority;

    /**
     * @var string|null
     */
    private $comment;

    /**
     * @var UriDefinitionMediaInterface|UriDefinitionMediaImage|UriDefinitionMediaVideo|null
     */
    private $media;

    /**
     * @param string                           $location
     * @param string|null                      $changeFrequency
     * @param float|null                       $priority
     * @param \DateTime|null                   $lastModified
     */
    public function __construct(string $location, string $changeFrequency = null, float $priority = 0.5, \DateTime $lastModified = null)
    {
        $this->setLocation($location);
        $this->setChangeFrequency($changeFrequency);
        $this->setPriority($priority);
        $this->setLastModified($lastModified);
    }

    /**
     * @param string|null $changeFrequency
     *
     * @return self
     */
    public function setChangeFrequency(string $changeFrequency = null): self
    {
        if (null !== $changeFrequency && !defined(sprintf('%s::CHANGE_FREQ_%s', UriDefinitionChangeFreqInterface::class, strtoupper($changeFrequency)))) {
            throw new InvalidArgumentException('Change frequency "%s" must be a valid constant from "%s"', $changeFrequency, UriDefinitionChangeFreqInterface::class);
        }

        $this->changeFrequency = $changeFrequency;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasChangeFrequency(): bool
    {
        return $this->changeFrequency !== null;
    }

    /**
     * @return string
     */
    public function getChangeFrequency(): ?string
    {
        return $this->changeFrequency;
    }

    /**
     * @param float $priority
     *
     * @return self
     */
    public function setPriority(float $priority): self
    {
        if ($priority > 1 || $priority < 0) {
            throw new InvalidArgumentException('Priority "%f" must be between 0 and 1.', $priority);
        }

        $this->priority = $priority;

        return $this;
    }

    /**
     * @return float
     */
    public function getPriority(): float
    {
        return $this->priority;
    }

    /**
     * @param \DateTime|null $lastModified
     *
     * @return self
     */
    public function setLastModified(\DateTime $lastModified = null): self
    {
        $this->lastModified = $this->sanitizeDateTime($lastModified);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLastModified(): bool
    {
        return $this->lastModified !== null;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastModified(): ?\DateTime
    {
        return $this->lastModified;
    }

    /**
     * @return null|string
     */
    public function getLastModifiedString(): ?string
    {
        return $this->formatDateTimeAsW3C($this->lastModified, $this->lastModifiedPrecise);
    }

    /**
     * @param bool $lastModifiedPrecise
     *
     * @return $this
     */
    public function setLastModifiedPrecise(bool $lastModifiedPrecise)
    {
        $this->lastModifiedPrecise = $lastModifiedPrecise;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLastModifiedPrecise(): bool
    {
        return $this->lastModifiedPrecise;
    }

    /**
     * @param string|null $comment
     *
     * @return self
     */
    public function setComment(string $comment = null): self
    {
        $this->comment = $this->toHtmlEntities($comment);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasComment(): bool
    {
        return $this->comment !== null;
    }

    /**
     * @return null|string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param UriDefinitionMediaInterface|null $media
     *
     * @return self
     */
    public function setMedia(UriDefinitionMediaInterface $media = null): self
    {
        if (null !== $media && !($media instanceof UriDefinitionMediaImage || $media instanceof UriDefinitionMediaVideo)) {
            throw new InvalidArgumentException('Media supported types include "%s" and "%s".', UriDefinitionMediaImage::class, UriDefinitionMediaVideo::class);
        }

        $this->media = $media;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasMedia(): bool
    {
        return $this->media !== null;
    }

    /**
     * @return bool
     */
    public function isMediaImage(): bool
    {
        return $this->hasMedia() && $this->media instanceof UriDefinitionMediaImage;
    }

    /**
     * @return bool
     */
    public function isMediaVideo(): bool
    {
        return $this->hasMedia() && $this->media instanceof UriDefinitionMediaVideo;
    }

    /**
     * @return null|UriDefinitionMediaInterface|UriDefinitionMediaImage|UriDefinitionMediaVideo
     */
    public function getMedia(): ?UriDefinitionMediaInterface
    {
        return $this->media;
    }
}
