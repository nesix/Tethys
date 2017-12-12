<?php

namespace Tethys\Console;

class Request extends \Tethys\Core\Request
{

    private $_params;

    public function getParams()
    {
        if (null === $this->_params) {
            if (isset($_SERVER['argv'])) {
                $this->_params = $_SERVER['argv'];
                array_shift($this->_params);
            } else {
                $this->_params = [];
            }
        }
        return $this->_params;
    }

    /**
     * @return array
     * @throws BadParameterException
     */
    public function resolve()
    {

        return \Tethys::$app->getRoutesManager()->parseRequest($this);

    }
}