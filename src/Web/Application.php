<?php

namespace Tethys\Web;


use Tethys\Core\Component;
use Tethys\Core\Controller;
use Tethys\Core\Exception;

/**
 * Class Application
 * @package Tethys\Web
 *
 * Web-приложение
 *
 */
class Application extends \Tethys\Core\Application
{

    /**
     * Обработка запроса
     *
     * @param Request $request
     * @return Component|Response
     * @throws BadRouteHttpException
     * @throws Exception
     * @throws \ReflectionException
     * @throws \Tethys\Core\BadRouteException
     * @throws \Tethys\Core\ControllerNotFoundException
     */
    public function handleRequest($request)
    {

        $response = $this->getResponse();

        try {

            $resolve = $request->resolve();
            if (!($resolve && is_array($resolve))) throw new BadRouteHttpException();

            $route = array_shift($resolve);
            $params = $resolve ? array_shift($resolve) : [];

            /**
             * @var Controller $controller
             */
            list($controller, $action) = $this->getController($route);
            if (!$controller) throw new BadRouteHttpException;

            ob_start();

            $result = $controller->runAction($action, $params) ?: 'empty';

            $output_response = ob_get_clean();

            if ($result instanceof Response) {

                return $result;

            } else {

                $response->result = $output_response . $result;

            }

        } catch (RedirectException $e) {

            $response->redirect($e->url, $e->permanent);

        }

        return $response;

    }

    /**
     * @return Response|Component
     * @throws \Tethys\Core\ComponentNotFoundException
     * @throws \Tethys\Core\UnknownComponentClassException
     * @throws \Tethys\Core\UnknownComponentException
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
            'request' => 'Tethys\Web\Request',
            'response' => 'Tethys\Web\Response',
            'view' => 'Tethys\Web\View',
            'log'  => 'Tethys\Web\Logging',
            'routing' => 'Tethys\Web\RoutesManager',
            'errorHandler' => 'Tethys\Web\ErrorHandler',
        ]);
    }

}