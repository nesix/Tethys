<?php

namespace Tethys\Core;


/**
 * Class Application
 * @package Tethys\Core
 *
 * Базовый класс приложения
 *
 */
abstract class Application extends Component
{

    /**
     * Корневая директория
     *
     * @var string
     */
    public $baseDir;

    /**
     * Настройки компонентов
     *
     * @var array
     */
    public $components;

    /**
     * Экземпляры объектов-компонентов
     *
     * @var array
     */
    protected $_components = [];

    /**
     * Application constructor.
     * @param array $row
     * @throws ComponentNotFoundException
     * @throws UnknownComponentClassException
     * @throws UnknownComponentException
     */
    public function __construct(array $row = [])
    {
        parent::__construct($row);

        \Tethys::$app = $this;

        if (null === $this->components) $this->components = [];
        foreach (static::defaultComponentsClasses() as $id=>$class) {
            if (!isset($this->components[$id])) $this->components[$id] = [];
            if (!isset($this->components[$id]['class'])) $this->components[$id]['class'] = $class;
        }

        $this->getErrorHandler()->register();
    }

    /**
     * Создает экземпляр компоненты
     *
     * @param string $id
     * @return Component
     * @throws ComponentNotFoundException
     * @throws UnknownComponentClassException
     * @throws UnknownComponentException
     */
    public function get($id)
    {
        if (!isset($this->_components[$id])) {

            if (!isset($this->components[$id])) throw new UnknownComponentException($id);

            $params = $this->components[$id];

            if (!isset($params['class'])) throw new UnknownComponentClassException($id);

            /**
             * @var Component|string $class
             */
            $class = $params['class'];
            unset($params['class']);

            if (!class_exists($class)) throw new ComponentNotFoundException($class);

            $this->_components[$id] = $class::make($params);

        }

        return $this->_components[$id];
    }

    /**
     * @return int
     */
    public function run()
    {
        try {

            $response = $this->handleRequest(\Tethys::request());

            $response->send();

            return $response->exitStatus;

        } catch (ExitException $e) {

            return $e->exitStatus;

        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    abstract public function handleRequest($request);

    /**
     * @param string $route
     * @return array
     * @throws BadRouteException
     * @throws ControllerNotFoundException
     * @throws \ReflectionException
     */
    public function getController($route)
    {

        $paramToCase = function ($param) {
            $ret = [];
            foreach (explode('-', $param) as $item) $ret[] = ucfirst($item);
            return implode($ret);
        };

        /** @var Controller|string $class */

        if (is_array($route)) {

            $class = array_shift($route);
            $action = array_shift($route);

        } elseif (is_string($route) && preg_match('#^(?<module>[\w\-]+)(\/(?<controller>[\w\-]+)(\/(?<action>[\w\-]+).*)?)?$#', $route, $matches)) {

            $class = 'Modules\\' . $paramToCase($matches['module']) . '\\Controllers\\' . $paramToCase($matches['controller'] ?? 'Index');
            $action = $paramToCase($matches['action'] ?? 'index');

        } else return [ null, null ];

        if (!class_exists($class)) throw new ControllerNotFoundException('Controller class ' . $class.' not found!');

        $reflection = new \ReflectionClass($class);
        if (!$reflection->isSubclassOf('Tethys\Core\Controller')) throw new BadRouteException('Class ~' . $class.'~ is not instance of ~Tethys\Core\Controller~!');

        return [ $class::make(), $action ];

    }

    /**
     * Маршрутизация
     *
     * @return Component|RoutesManager
     * @throws ComponentNotFoundException
     * @throws UnknownComponentClassException
     * @throws UnknownComponentException
     */
    public function getRoutesManager()
    {
        return $this->get('routing');
    }

    /**
     * Обработчик ошибок
     *
     * @return Component|ErrorHandler
     * @throws ComponentNotFoundException
     * @throws UnknownComponentClassException
     * @throws UnknownComponentException
     */
    public function getErrorHandler()
    {
        return $this->get('errorHandler');
    }

    /**
     * Возвращает классы компонент "по-умолчанию"
     *
     * @return array
     */
    public static function defaultComponentsClasses()
    {
        return [
            'security' => 'Tethys\Core\Security',
            'storage' => 'Tethys\Utils\Redis',
        ];
    }

}