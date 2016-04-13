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

namespace LpDigital\Bundle\GoogleCustomSearchBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use BackBee\ClassContent\Gcse\SearchBox;
use BackBee\ClassContent\Gcse\SearchBoxApi;
use BackBee\Event\Event;
use BackBee\Renderer\AbstractRenderer;

use LpDigital\Bundle\GoogleCustomSearchBundle\GoogleCustomSearch;

/**
 * Event listener for the Google custom search bundle.
 *
 * @author Charles Rouillon <charles.rouilon@lp-digital.fr>
 */
class GcseListener implements EventSubscriberInterface
{

    /**
     * The Google custom search bundle.
     *
     * @var GoogleCustomSearch
     */
    private $gcse;

    /**
     * Service listener constructor.
     *
     * @param GoogleCustomSearch $gcse The google custom search bundle.
     */
    public function __construct(GoogleCustomSearch $gcse)
    {
        $this->gcse = $gcse;
    }

    /**
     * Assigns the Google Custom Search bundle to the renderer.
     * Occures on gcse.searchbox.prerender events.
     *
     * @param Event $event
     */
    public function onPrerenderSearchBox(Event $event)
    {
        if (!($event->getEventArgs() instanceof AbstractRenderer)) {
            return;
        }

        $event->getEventArgs()->assign('gcse', $this->gcse);
    }

    /**
     * Apply default paramters to newly created search box.
     * Occures on gcse.searchbox.prepersist events.
     *
     * @param Event $event
     */
    public function onPrepersistSearchBox(Event $event)
    {
        $content = $event->getTarget();
        if (!($content instanceof SearchBox)) {
            return;
        }

        foreach ($this->gcse->getDefaultParameters() as $key => $value) {
            $key = strtolower($key);
            if (!$content->hasParam($key)) {
                continue;
            }

            if (is_array($content->getParamValue($key))) {
                $value = (array) $value;
            }

            $content->setParam($key, $value);
        }
    }

    /**
     * Assigns the Google Custom Search bundle to the renderer.
     * Occures on gcse.searchboxapi.render events.
     *
     * @param Event $event
     */
    public function onRenderSearchBoxApi(Event $event)
    {
        $content = $event->getTarget();
        if (!($content instanceof SearchBoxApi)) {
            return;
        }

        $renderer = $event->getEventArgs();
        if (!($renderer instanceof AbstractRenderer)) {
            return;
        }

        $queryName = $this->gcse->getParameter('queryParameterName', $content);
        $query = $event->getApplication()->getRequest()->get($queryName);
        if (empty($query)) {
            return;
        }

        $parameters = $this->computeAPIParameters($event);

        $renderer->assign('query', $query);
        $renderer->assign('parameters', $parameters);
        $renderer->assign('results', $this->gcse->query($query, $parameters));
    }

    /**
     * Formats the array of parameters to match the API reference.
     *
     * @param  Event $event The current rendering event.
     *
     * @return array        The formatted parameters.
     */
    private function computeAPIParameters(Event $event)
    {
        $num = $this->gcse->getParameter('resultSetSize', $event->getTarget());

        $parameters = [
            'cx' => $this->gcse->getDefaultParameter('cx'),
            'num' => is_numeric($num) ? intval($num) : 10,
            'start' => $event->getApplication()->getRequest()->query->get('start', 1),
            'filter' => 1
        ];

        return $parameters;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to.
     */
    public static function getSubscribedEvents()
    {
        return [
            'gcse.searchbox.prepersist' => 'onPrepersistSearchBox',
            'gcse.searchbox.prerender' => 'onPrerenderSearchBox',
            'gcse.searchbox.render' => 'onRenderSearchBoxApi'
        ];
    }
}
