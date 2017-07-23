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

class RobotsRegistry implements \IteratorAggregate, \Countable
{
    /**
     * @var UserAgentEntry[]
     */
    private $userAgentEntries;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->userAgentEntries = array_map(function (array $c) use ($config) {
            $userAgent = new UserAgentEntry($c['user_agent'], $c['allow'], $c['disallow']);

            if ($c['merge_default'] && isset($config['*'])) {
                $userAgent->mergeAllowList($config['*']['allow']);
                $userAgent->mergeDisallowList($config['*']['disallow']);
            }

            return $userAgent;
        }, array_filter($config, function (array $c) {
            return isset($c['user_agent'], $c['allow'], $c['disallow']);
        }));
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->userAgentEntries);
    }

    /**
     * @return \ArrayIterator|UserAgentEntry[]
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->getUserAgentEntries());
    }

    /**
     * @return UserAgentEntry[]
     */
    public function getUserAgentEntries(): array
    {
        return $this->userAgentEntries;
    }
}
