<?php

/*
 * Copyright (c) 2016 Lp digital system
 *
 * This file is part of GoogleCustomSearchBundle.
 *
 * GoogleCustomSearchBundle is free bundle: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GoogleCustomSearchBundle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GoogleCustomSearchBundle. If not, see <http://www.gnu.org/licenses/>.
 */

namespace LpDigital\Bundle\GoogleCustomSearchBundle\Tests;

use BackBee\ClassContent\Gcse\SearchBox;

/**
 * Tests suite for LpDigital\Bundle\GoogleCustomSearchBundle\GoogleCustomSearch.
 *
 * @author Charles Rouillon <charles.rouillon@lp-digital.fr>
 * @covers LpDigital\Bundle\GoogleCustomSearchBundle\GoogleCustomSearch
 */
class GoogleCustomSearchTest extends GcseTestCase
{

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\GoogleCustomSearch::getDefaultParameters()
     */
    public function testGetDefaultParameters()
    {
        $expected = [
            'cx' => null,
            'queryParameterName' => 'q',
            'resultsUrl' => null,
            'resultSetSize' => 'large',
            'autoSearchOnLoad' => true,
            'enableAutoComplete' => true,
            'disableWebSearch' => null,
            'enableImageSearch' => null
        ];

        $this->assertEquals($expected, $this->gcse->getDefaultParameters());
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\GoogleCustomSearch::getDefaultParameter()
     */
    public function testGetDefaultParameter()
    {
        $this->assertEquals('q', $this->gcse->getDefaultParameter('queryParameterName'));
        $this->assertNull($this->gcse->getDefaultParameter('unknownParameter'));
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\GoogleCustomSearch::getParameter()
     */
    public function testGetParameter()
    {
        $this->assertEquals('q', $this->gcse->getParameter('queryParameterName'));

        $searchBox = new SearchBox();
        $searchBox->setParam('queryparametername', 'query');
        $this->assertEquals('query', $this->gcse->getParameter('queryParameterName', $searchBox));

        $this->gcse->getConfig()->setSection('gcse', ['default_parameters' => ['cx' => 'fake_engine_id']], false);
        $this->assertEquals('fake_engine_id', $this->gcse->getParameter('cx', $searchBox));
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\GoogleCustomSearch::getDeveloperKey()
     */
    public function testGetDeveloperKey()
    {
        $this->gcse->getConfig()->setSection('gcse', ['developer_key' => 'fake_developer_key'], false);
        $this->assertEquals('fake_developer_key', $this->gcse->getDeveloperKey());
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\GoogleCustomSearch::getEngine()
     */
    public function testGetEngine()
    {
        $engine = $this->gcse->getEngine('Fake app name');
        $this->assertInstanceOf('\Google_Service_Customsearch', $engine);
        $this->assertEquals('Fake app name', $engine->getClient()->getApplicationName());
    }
}
