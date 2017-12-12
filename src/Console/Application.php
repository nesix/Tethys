<?php

namespace Tethys\Console;

use Tethys\Core\BadRouteException;
use Tethys\Core\Component;
use Tethys\Core\Controller;
use Tethys\Core\Exception;

class Application extends \Tethys\Core\Application
{

    public $controllers;

    /**
     * @param Request $request
     * @return Component|Response
     * @throws Exception
     */
    public function handleRequest($request)
    {

        $response = $this->getResponse();

        list($route, $params) = $request->resolve();

        /** @var Controller $controller */
        list($controller, $action) = $this->getController($route);

        if (!$controller) throw new BadRouteException(1, 'Bad parameters');

        $response->result = $controller->runAction($action, $params);

        return $response;

    }

    /**
     * @return Response|Component
     */
    public function getResponse()
    {
        return $this->get('response');
    }

    /**
     * @return array
     */
    public static function defaultComponentsClasses()
    {
        return array_merge(parent::defaultComponentsClasses(), [
            'request' => 'Tethys\Console\Request',
            'response' => 'Tethys\Console\Response',
            'log'  => 'Tethys\Console\Logging',
            'routing' => 'Tethys\Console\RoutesManager',
            'errorHandler' => 'Tethys\Console\ErrorHandler',
        ]);
    }

}