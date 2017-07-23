<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Registry\Robots;

class UserAgentEntry
{
    /**
     * @var string
     */
    private $userAgent;

    /**
     * @var string[]
     */
    private $allowList;

    /**
     * @var string[]
     */
    private $disallowList;

    /**
     * @param string   $userAgent
     * @param string[] $allowList
     * @param string[] $disallowList
     */
    public function __construct(string $userAgent, array $allowList, array $disallowList)
    {
        $this->userAgent = $userAgent;
        $this->allowList = $allowList;
        $this->disallowList = $disallowList;
    }

    /**
     * @param array $allow
     */
    public function mergeAllowList(array $allow)
    {
        $this->allowList = array_unique(array_merge($allow, $this->allowList));
    }

    /**
     * @param array $allow
     */
    public function mergeDisallowList(array $allow)
    {
        $this->disallowList = array_unique(array_merge($allow, $this->disallowList));
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @return string[]
     */
    public function getAllowList(): array
    {
        return $this->allowList;
    }

    /**
     * @return array
     */
    public function getDisallowList(): array
    {
        return $this->disallowList;
    }
}
