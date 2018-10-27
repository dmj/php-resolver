<?php

/**
 * This file is part of HAB Resolver.
 *
 * HAB Resolver is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * HAB Resolver is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HAB Resolver.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */

namespace HAB\Resolver;

use Psr\Http\Message\UriInterface as Uri;

/**
 * Resolve relative URI using the algorithm defined in RFC3986, Section 5.
 *
 * @author David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class RFC3986 implements Resolver
{
    public function resolve (Uri $href, Uri $base)
    {
        $target = clone($href);
        if ($href->getScheme()) {
            $target = $target->withPath($this->removeDotSegments($href->getPath()));
        } else {
            if ($href->getAuthority()) {
                $target = $target->withPath($this->removeDotSegments($href->getPath()));
            } else {
                if (!$href->getPath()) {
                    $target = $target->withPath($base->getPath());
                    if (!$href->getQuery()) {
                        $target = $target->withQuery($base->getQuery());
                    }
                } else {
                    if (substr($href->getPath(), 0, 1) === '/') {
                        $target = $target->withPath($this->removeDotSegments($href->getPath()));
                    } else {
                        $target = $target->withPath($this->removeDotSegments($this->mergePath($base, $href)));
                    }
                }
                $target = $target->withHost($base->getHost())->withPort($base->getPort())->withUserInfo($base->getUserInfo());
            }
            $target = $target->withScheme($base->getScheme());
        }
        return $target;
    }

    public function mergePath (Uri $base, Uri $href)
    {
        if ($base->getAuthority() && !$base->getPath()) {
            return '/' . $href->getPath();
        }
        $segments = explode('/', $base->getPath());
        if ($segments) {
            array_pop($segments);
        }
        $segments []= $href->getPath();
        return implode('/', $segments);
    }

    public function removeDotSegments ($path)
    {

        $segments = explode('/', $path);
        $output = array();
        if ($segments) {
            while ($segments[0] === '.' || $segments[0] === '..') {
                array_shift($segments);
            }
        }
        foreach ($segments as $segment) {
            switch ($segment) {
            case '.':
                break;
            case '..':
                array_pop($output);
                break;
            default:
                $output []= $segment;
            }
        }
        if ($segment === '..' or $segment === '.') {
            $output []= '';
        }
        return implode('/', $output);
    }
}
