<?php

namespace Tethys\Core;

class Controller extends Component implements ViewContextInterface
{

    protected $view;

//    protected $params;

    /**
     * @param string $action
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function runAction($action, $params = [])
    {
        $method = 'action'.ucfirst($action);
        if (!method_exists($this, $method)) throw new Exception('Bad method call '.get_class($this).'::'.$method);
        $this->obtain($params ?: []);
        //$this->params = $params;
        return call_user_func([ $this, $method ]);
    }

    /**
     * @return string
     */
    public function getViewPath()
    {
        $class = new \ReflectionClass($this);
        return dirname($class->getFileName(), 2).DIRECTORY_SEPARATOR.'View';
    }

    /**
     * @param string $template
     * @param array $data
     * @return mixed
     */
    public function renderView($template, $data = [])
    {
        $content = $this->getView()->render($template, $data, $this);
        return $this->renderLayout($content);
    }

    public function renderLayout($content)
    {
        $file = $this->getView()->layouts . $this->getLayout() . '.' . $this->getView()->defaultExtension;
        return $this->getView()->renderFile($file, [ 'content' => $content ] + $this->getLayoutData(), $this);
    }

    public function getLayout()
    {
        return 'page';
    }

    public function getLayoutData()
    {
        return [];
    }

    public function getView()
    {
        if (null === $this->view) $this->view = \Tethys::view();
        return $this->view;
    }

    public function __get($name)
    {
        if (isset($this->params[$name])) return $this->params[$name];
        return parent::__get($name);
    }

}