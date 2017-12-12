<?php

namespace Tethys\Web;

use Tethys\Core\Component;
use Tethys\Core\ViewContextInterface;

abstract class Widget extends Component implements ViewContextInterface
{

    /**
     * @return string
     */
    abstract public function run();

    /**
     * @return string
     */
    public function getViewPath()
    {
        $class = new \ReflectionClass($this);
        return dirname($class->getFileName()).DIRECTORY_SEPARATOR.'View';
    }

    /**
     * @param string $template
     * @param array $data
     * @return mixed
     */
    public function renderView($template, $data = [])
    {
        return $this->getView()->render($template, $data, $this);
    }

    /** @var View */
    private $view;

    /**
     * @return \Tethys\Core\View|View
     */
    public function getView()
    {
        if (null === $this->view) $this->view = \Tethys::view();
        return $this->view;
    }

    /**
     * @param array $params
     * @return string
     */
    public static function widget(array $params = [])
    {
        return static::make($params)->run();
    }

}