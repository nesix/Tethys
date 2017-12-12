<?php

namespace Tethys\Web;

use Tethys\Core\Exception;

class RoutesManager extends \Tethys\Core\RoutesManager
{

    public $routes;

    public $defaultClass = 'Tethys\Web\Routes';

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function parseRequest($request)
    {

        foreach ($this->routes as $params) {

            if (!is_array($params)) continue;

            if (isset($params['class'])) {

                $class = $params['class'];
                unset($params['class']);

            } else {

                $class = $this->defaultClass;

            }

            /** @var string|Routes $class */
            $route = $class::make($params);

            if (!($route instanceof Routes)) continue;

            $result = $route->parseRequest($request);
            if (false !== $result) return $result;

        }

        throw new BadRouteHttpException('Не удалось найти правило');

    }

}