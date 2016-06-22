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

namespace LpDigital\Bundle\GoogleCustomSearchBundle\Tests\Controller;

use LpDigital\Bundle\GoogleCustomSearchBundle\Tests\GcseTestCase;
use LpDigital\Bundle\GoogleCustomSearchBundle\Tests\Mock\MockAdminController;

/**
 * Tests suite for LpDigital\Bundle\GoogleCustomSearchBundle\Controller\AdminController.
 *
 * @author Charles Rouillon <charles.rouillon@lp-digital.fr>
 * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Controller\AdminController
 */
class AdminControllerTest extends GcseTestCase
{

    /**
     * @var MockAdminController
     */
    private $controller;

    /**
     * Sets up the required fixtures.
     */
    public function setUp()
    {
        parent::setUp();
        $this->controller = new MockAdminController($this->gcse->getApplication());
        $this->controller->setBundle($this->gcse);
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Controller\AdminController::indexAction()
     */
    public function testIndexAction()
    {
        $response = $this->controller->indexAction();
        $this->assertTrue(is_string($response));
        $this->assertEquals('<div', substr($response, 0, 4));
        $this->assertEquals('div>', substr($response, -4));
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Controller\AdminController::saveAction()
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException
     */
    public function testSaveActionWithoutToken()
    {
        $this->controller->saveAction();
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Controller\AdminController::saveAction()
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Controller\AdminController::persistConfig()
     */
    public function testSaveActionStopped()
    {
        $this->resetAclSchema();
        $this->createAuthenticatedUser();

        $request = [
            'cx' => 'fake_engine_id',
            'developer_key' => 'fake_developer_key'
        ];
        $this->controller->getRequest()->request->replace($request);
        $this->controller->resetNotifications();
        $this->controller->saveAction();

        $expected = [[
        'type' => 'error',
        'message' => 'Configuration not saved: Application is not started, we are not able to persist multiple site config.'
        ]];

        $this->assertEquals($expected, $this->controller->getNotifications());
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Controller\AdminController::saveAction()
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Controller\AdminController::persistConfig()
     */
    public function testSaveActionStarted()
    {
        $this->resetAclSchema();
        $this->createAuthenticatedUser();

        $this->gcse->getConfig()->setSection('bundle', ['config_per_site' => false], false);
        $this->gcse->getApplication()->start();

        $request = [
            'cx' => 'fake_engine_id',
            'developer_key' => 'fake_developer_key'
        ];
        $this->controller->getRequest()->request->replace($request);
        $this->controller->resetNotifications();
        $response = $this->controller->saveAction();

        $this->assertEquals($response, $this->controller->indexAction());
        $this->assertEquals($request['cx'], $this->gcse->getDefaultParameter('cx'));
        $this->assertEquals($request['developer_key'], $this->gcse->getDeveloperKey());

        $expected = [[
        'type' => 'success',
        'message' => 'Configuration saved.'
        ]];

        $this->assertEquals($expected, $this->controller->getNotifications());
    }

    /**
     * @covers LpDigital\Bundle\GoogleCustomSearchBundle\Controller\AdminController::testAction()
     */
    public function testTestAction()
    {
        $response = $this->controller->testAction();
        $this->assertTrue(is_string($response));
        $this->assertEquals('keyInvalid', $response);
    }
}
