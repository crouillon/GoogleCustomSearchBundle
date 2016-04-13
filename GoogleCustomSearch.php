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

namespace LpDigital\Bundle\GoogleCustomSearchBundle;

use BackBee\Bundle\AbstractBundle;
use BackBee\ClassContent\AbstractContent;
use BackBee\Utils\Collection\Collection;

/**
 * Add Google Custom Search feature in BackBee
 *
 * @author Charles Rouillon <charles.rouilon@lp-digital.fr>
 */
class GoogleCustomSearch extends AbstractBundle
{

    /**
     * The default application name used while querying Google API.
     */
    const DEFAULT_APPLICATION_NAME = 'Google Custom Search Bundle';

    /**
     * The Google Custom Search engine.
     *
     * @var \Google_Service_Customsearch
     */
    private $engine;

    /**
     * Calls the bundle if got for the first time.
     */
    public function start()
    {

    }

    /**
     * Calls before the bundle is stopped or destroyed.
     *
     * @codeCoverageIgnore
     */
    public function stop()
    {

    }

    /**
     * Gets the $param parameter value for $content object if provided, default
     * parameter value otherwise.
     *
     * @param  string               $param   The parameter name.
     * @param  AbstractContent|null $content Optional, the content object from
     *                                       which try to read the parameter value.
     *
     * @return string|null                   The paramter value if found, null otherwise.
     */
    public function getParameter($param, AbstractContent $content = null)
    {
        $bbParam = strtolower($param);
        if (null !== $content && $content->hasParam($bbParam)) {
            $value = $content->getParamValue($bbParam);

            return is_array($value) ? reset($value) : $value;
        }

        return $this->getDefaultParameter($param);
    }

    /**
     * Returns the default parameter $param if exists, null otherwise.
     *
     * @param  string      $param The parameter name to look for.
     *
     * @return string|null        The paramter value if found, null otherwise.
     */
    public function getDefaultParameter($param)
    {
        return Collection::get($this->getConfig()->getGcseConfig(), 'default_parameters:' . $param);
    }

    /**
     * Returns an array of default parameters.
     *
     * @return array|null The default parameters if exists, null otherwise
     */
    public function getDefaultParameters()
    {
        return Collection::get($this->getConfig()->getGcseConfig(), 'default_parameters');
    }

    /**
     * Returns the developer key.
     *
     * @return string
     */
    public function getDeveloperKey()
    {
        return Collection::get($this->getConfig()->getGcseConfig(), 'developer_key');
    }

    /**
     * Invokes the Google Custom Search engine.
     *
     * @param  string $query      The search query.
     * @param  array  $parameters The search parameters.
     *
     * @return \Google_Customsearch_Search|null
     *
     * @codeCoverageIgnore
     */
    public function query($query, array $parameters)
    {
        try {
            return $this->getEngine()->cse->listCse($query, $parameters);
        } catch (\Google_Service_Exception $ex) {
            $this->getApplication()->error($ex->getMessage());
        }

        return null;
    }

    /**
     * Gets the Custom Search engine.
     *
     * @param  string $appname      Optional, the application name.
     * @param  string $developerKey Optional, the developer key.
     *
     * @return \Google_Service_Customsearch
     */
    public function getEngine($appname = null, $developerKey = null)
    {
        if (null === $this->engine) {
            $client = new \Google_Client();
            $client->setApplicationName($appname ? : self::DEFAULT_APPLICATION_NAME);
            $client->setDeveloperKey($developerKey ? : $this->getDeveloperKey());

            $this->engine = new \Google_Service_Customsearch($client);
        }

        return $this->engine;
    }
}
