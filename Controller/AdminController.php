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

namespace LpDigital\Bundle\GoogleCustomSearchBundle\Controller;

use BackBee\Bundle\AbstractAdminBundleController;

/**
 * Bundle administration controller.
 *
 * @author Charles Rouillon <charles.rouillon@lp-digital.fr>
 */
class AdminController extends AbstractAdminBundleController
{

    /**
     * Main administration form.
     *
     * @return string Index template rendering.
     */
    public function indexAction()
    {
        return $this->render('Gcse/Admin/Index.twig', ['gcse' => $this->getBundle()]);
    }

    /**
     * Save configuration.
     *
     * @return string Index template rendering.
     */
    public function saveAction()
    {
        $this->granted('EDIT', $this->getBundle());

        $request = $this->getRequest()->request;
        $config = array_merge(['default_parameters' => []], $this->getBundle()->getConfig()->getGcseConfig());

        $config['default_parameters']['cx'] = $request->get('cx');
        $config['default_parameters']['queryParameterName'] = $request->get('queryParameterName');
        $config['default_parameters']['resultsUrl'] = $request->get('resultsUrl');
        $config['default_parameters']['resultSetSize'] = $request->get('resultSetSize');
        $config['default_parameters']['autoSearchOnLoad'] = $request->get('autoSearchOnLoad');
        $config['default_parameters']['enableAutoComplete'] = $request->get('enableAutoComplete');
        $config['default_parameters']['disableWebSearch'] = $request->get('disableWebSearch');
        $config['default_parameters']['enableImageSearch'] = $request->get('enableImageSearch');

        $config['developer_key'] = $request->get('developer_key');

        return $this->persistConfig($config)->indexAction();
    }

    /**
     * Test a Google Client connection.
     *
     * @return string ok if the connection succeed, the error message otherwise.
     */
    public function testAction()
    {
        $message = 'ok';
        $request = $this->getRequest()->request;

        try {
            $this->getBundle()->getEngine('Test Call API', $request->get('developer_key'))->cse->listCse('test', ['cx' => $request->get('cx')]);
        } catch (\Google_Service_Exception $ex) {
            $message = 'unknown';
            $errors = $ex->getErrors();
            if (403 === $ex->getCode()) {
                $message = 'keyInvalid';
            } elseif (!empty($errors) && isset($errors[0]['reason'])) {
                $message = $errors[0]['reason'];
            }
        }

        return $message;
    }

    /**
     * Persists configuration.
     *
     * @param  array $config The array configuration to be persisted.
     *
     * @return AdminController
     */
    private function persistConfig(array $config)
    {
        try {
            $bundle = $this->getBundle();
            $bundle->getConfig()->setSection('gcse', $config, true);
            $bundleConfig = $bundle->getConfig()->getBundleConfig();

            $this->getContainer()->get('config.persistor')->persist(
                    $bundle->getConfig(), isset($bundleConfig['config_per_site']) ? $bundleConfig['config_per_site'] : false
            );
            $this->notifyUser(self::NOTIFY_SUCCESS, 'Configuration saved.');
        } catch (\Exception $e) {
            $this->notifyUser(self::NOTIFY_ERROR, 'Configuration not saved: ' . $e->getMessage());
        }

        return $this;
    }
}
