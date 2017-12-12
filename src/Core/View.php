<?php

namespace Tethys\Core;

abstract class View extends Component
{

    public $defaultExtension = 'php';

    public $layouts = '';

    public $context;

    public function render($template, $data, $context = null)
    {
        $viewFile = $this->findViewFile($template, $context);
        return $this->renderFile($viewFile, $data, $context);
    }

    public function findViewFile($template, $context)
    {
        if ($context instanceof ViewContextInterface) {

            $file = $context->getViewPath() . DIRECTORY_SEPARATOR . $template;

        } else throw new InvalidCallException();

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }

        $path = $file . '.' . $this->defaultExtension;

        if ($this->defaultExtension !== 'php' && !is_file($path)) {
            $path = $file . '.php';
        }

        return $path;
    }

    public function renderFile($file, $data, $context = null)
    {

        $this->context = $context;

        ob_start();
        ob_implicit_flush(false);
        extract($data, EXTR_OVERWRITE);
        /** @noinspection PhpIncludeInspection */
        require($file);
        return ob_get_clean();
    }

}
