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

namespace LpDigital\Bundle\GoogleCustomSearchBundle\Tests\Listener;

use BackBee\ClassContent\Gcse\SearchBoxApi;
use BackBee\ClassContent\Tests\Mock\MockContent;
use BackBee\Event\Event;

use LpDigital\Bundle\GoogleCustomSearchBundle\Listener\GcseListener;
use LpDigital\Bundle\GoogleCustomSearchBundle\Tests\GcseTestCase;

/**
 * Tests suite for LpDigital\Bundle\GoogleCustomSearchBundle\Listener\GcseListener.
 *
 * @author Charles Rouillon <charles.rouilon@lp-digital.fr>
 * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Listener\GcseListener
 */
class GcseListenerTest extends GcseTestCase
{

    /**
     * @var GcseListener
     */
    private $listener;

    /**
     * Sets up the required fixtures.
     */
    public function setUp()
    {
        parent::setUp();
        $this->listener = $this->gcse->getApplication()->getContainer()->get('gcse.renderer.listener');
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Listener\GcseListener::onPrerenderSearchBox()
     */
    public function testOnPrerenderSearchBox()
    {
        $renderer = $this->gcse->getApplication()->getContainer()->get('renderer');

        $this->assertNull($this->listener->onPrerenderSearchBox(new Event(new SearchBoxApi())));
        $this->assertEquals([], $renderer->getAssignedVars());
        $this->assertNull($this->listener->onPrerenderSearchBox(new Event(new SearchBoxApi(), $renderer)));
        $this->assertEquals(['gcse' => $this->gcse], $renderer->getAssignedVars());
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Listener\GcseListener::onPrepersistSearchBox()
     */
    public function testOnPrepersistSearchBox()
    {
        $searchBox = new SearchBoxApi();
        $searchBox->setParam('queryparametername', 'query');

        $this->assertNull($this->listener->onPrepersistSearchBox(new Event(new MockContent())));
        $this->assertNull($this->listener->onPrepersistSearchBox(new Event($searchBox)));
        $this->assertEquals('q', $searchBox->getParamValue('queryparametername'));
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Listener\GcseListener::onRenderSearchBoxApi()
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Listener\GcseListener::computeAPIParameters()
     */
    public function testOnRenderSearchBoxApi()
    {
        $searchBox = new SearchBoxApi();
        $renderer = $this->gcse->getApplication()->getContainer()->get('renderer');
        $event = new Event($searchBox, $renderer);
        $event->setDispatcher($this->gcse->getApplication()->getContainer()->get('event.dispatcher'));

        $this->assertNull($this->listener->onRenderSearchBoxApi(new Event(new MockContent())));
        $this->assertEquals([], $renderer->getAssignedVars());
        $this->assertNull($this->listener->onRenderSearchBoxApi(new Event($searchBox)));
        $this->assertEquals([], $renderer->getAssignedVars());
        $this->assertNull($this->listener->onRenderSearchBoxApi($event));
        $this->assertEquals([], $renderer->getAssignedVars());

        $defaultExpected = [
            'query' => 'fake',
            'parameters' => [
                'cx' => null,
                'num' => 10,
                'start' => 1,
                'filter' => 1
            ],
            'results' => null
        ];

        $this->gcse->getApplication()->getRequest()->query->set('q', 'fake');
        $this->assertNull($this->listener->onRenderSearchBoxApi($event));
        $this->assertEquals($defaultExpected, $renderer->getAssignedVars());
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Listener\GcseListener::onRenderSearchBoxApi()
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Listener\GcseListener::computeAPIParameters()
     */
    public function testOnRenderSearchBoxApiSpec()
    {
        $searchBox = new SearchBoxApi();
        $searchBox->setParam('resultsetsize', '20');

        $renderer = $this->gcse->getApplication()->getContainer()->get('renderer');
        $event = new Event($searchBox, $renderer);
        $event->setDispatcher($this->gcse->getApplication()->getContainer()->get('event.dispatcher'));

        $specExpected = [
            'query' => 'fake',
            'parameters' => [
                'cx' => null,
                'num' => 20,
                'start' => '10',
                'filter' => 1
            ],
            'results' => null
        ];

        $this->gcse->getApplication()->getRequest()->query->set('q', 'fake');
        $this->gcse->getApplication()->getRequest()->query->set('start', '10');
        $this->listener->onRenderSearchBoxApi($event);
        $this->assertEquals($specExpected, $renderer->getAssignedVars());
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Listener\GcseListener::getSubscribedEvents()
     */
    public function testGetSubscribedEvents()
    {
        $expected = [
            'gcse.searchbox.prepersist' => 'onPrepersistSearchBox',
            'gcse.searchbox.prerender' => 'onPrerenderSearchBox',
            'gcse.searchbox.render' => 'onRenderSearchBoxApi'
        ];

        $this->assertEquals($expected, $this->listener->getSubscribedEvents());
    }
}
