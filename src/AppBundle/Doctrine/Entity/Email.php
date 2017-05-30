<?php

/*
 * This file is part of the `src-run/srw-client-silverpapillon` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Doctrine\Entity;

use Rf\AppBundle\Doctrine\Entity\Traits\IdentityIdTrait;
use Rf\AppBundle\Doctrine\Entity\Traits\TimestampableTrait;
use WhiteOctober\SwiftMailerDBBundle\EmailInterface;

class Email implements EmailInterface
{
    use IdentityIdTrait;
    use TimestampableTrait;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $message;

    /**
     * @param $status string
     *
     * @return self
     */
    public function setStatus($status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param $environment string
     *
     * @return self
     */
    public function setEnvironment($environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @param $message string
     *
     * @return self
     */
    public function setMessage($message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return \Swift_Message
     */
    public function getMessageInstance(): \Swift_Message
    {
        return unserialize($this->message);
    }
}
