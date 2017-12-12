<?php

namespace Tethys\Console;

use Tethys\Core\BadRouteException;
use Tethys\Core\Exception;

class RoutesManager extends \Tethys\Core\RoutesManager
{

    public $rules;

    public $defaultRules = [
        'help' => [ 'Tethys\Console\Controllers\HelpController', 'index' ],
    ];

    public $defaultRoute = 'help';

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function parseRequest($request)
    {

        if (null === $this->rules) throw new ExpectedRulesException;
        if (!is_array($this->rules)) throw new ExpectedRulesException;

        $rawParams = $request->getParams();

        $route = null;

        $params = [];
        $currentParamName = null;
        foreach ($rawParams as $param) {
            if (preg_match('/^(\-\-?)(?<param>\w+)([=\:](?<value>.+))?$/', $param, $matches)) {
                if (null === $route) $route = $this->defaultRoute;
                if (isset($matches['value'])) {
                    if ($currentParamName) {
                        $params[$currentParamName] = true;
                        $currentParamName = null;
                    }
                    $params[$matches['param']] = $matches['value'];
                } else {
                    if ($currentParamName) $params[$currentParamName] = true;
                    $currentParamName = $matches['param'];
                }
            } else {
                if (null === $route) $route = $param;
                elseif ($currentParamName) {
                    $params[$currentParamName] = $param;
                    $currentParamName = null;
                } else {
                    $params[$param] = true;
                }
            }
        }

        if ($currentParamName) $params[$currentParamName] = true;

        $route = $route ?: $this->defaultRoute;
        $rules = array_merge($this->defaultRules, $this->rules);

        if (isset($rules[$route])) return [ $rules[$route], $params ];

        throw new BadRouteException('Не найдено правило для маршрутизации');

    }

}