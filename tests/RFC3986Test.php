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

use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Unit tests for the RFC3986 resolver class.
 *
 * @author David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class RFC3986Test extends TestCase
{
    protected $normalExamples = array(
        'g:h'           => 'g:h',
        'g'             => 'http://a/b/c/g',
        './g'           => 'http://a/b/c/g',
        'g/'            => 'http://a/b/c/g/',
        '/g'            => 'http://a/g',
        '//g'           => 'http://g',
        '?y'            => 'http://a/b/c/d;p?y',
        'g?y'           => 'http://a/b/c/g?y',
        '#s'            => 'http://a/b/c/d;p?q#s',
        'g#s'           => 'http://a/b/c/g#s',
        'g?y#s'         => 'http://a/b/c/g?y#s',
        ';x'            => 'http://a/b/c/;x',
        'g;x'           => 'http://a/b/c/g;x',
        'g;x?y#s'       => 'http://a/b/c/g;x?y#s',
        ''              => 'http://a/b/c/d;p?q',
        '.'             => 'http://a/b/c/',
        './'            => 'http://a/b/c/',
        '..'            => 'http://a/b/',
        '../'           => 'http://a/b/',
        '../g'          => 'http://a/b/g',
        '../..'         => 'http://a/',
        '../../'        => 'http://a/',
        '../../g'       => 'http://a/g',
    );

    protected $abnormalExamples = array(
        '../../../g'    =>  'http://a/g',
        '../../../../g' =>  'http://a/g',
        '/./g'          =>  'http://a/g',
        '/../g'         =>  'http://a/g',
        'g.'            =>  'http://a/b/c/g.',
        '.g'            =>  'http://a/b/c/.g',
        'g..'           =>  'http://a/b/c/g..',
        '..g'           =>  'http://a/b/c/..g',
        './../g'        =>  'http://a/b/g',
        './g/.'         =>  'http://a/b/c/g/',
        'g/./h'         =>  'http://a/b/c/g/h',
        'g/../h'        =>  'http://a/b/c/h',
        'g;x=1/./y'     =>  'http://a/b/c/g;x=1/y',
        'g;x=1/../y'    =>  'http://a/b/c/y',
        'g?y/./x'       =>  'http://a/b/c/g?y/./x',
        'g?y/../x'      =>  'http://a/b/c/g?y/../x',
        'g#s/./x'       =>  'http://a/b/c/g#s/./x',
        'g#s/../x'      =>  'http://a/b/c/g#s/../x',
    );

    public function testNormaleExamples ()
    {

        $resolver = new RFC3986();
        $base = new Uri('http://a/b/c/d;p?q');
        foreach ($this->normalExamples as $rel => $res) {
            $href = new Uri($rel);
            $this->assertEquals($res, $resolver->resolve($href, $base));
        }
    }

    public function testAbnormaleExamples ()
    {

        $resolver = new RFC3986();
        $base = new Uri('http://a/b/c/d;p?q');
        foreach ($this->abnormalExamples as $rel => $res) {
            $href = new Uri($rel);
            $this->assertEquals($res, (string)$resolver->resolve($href, $base));
        }
    }
}
