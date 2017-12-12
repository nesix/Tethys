<?php

namespace Tethys\Core;

abstract class Request extends Component
{

    public $validatorClass = 'Tethys\Core\Validator';

    /**
     * @return string|Validator
     */
    public function getValidatorClass()
    {
        return $this->validatorClass;
    }

    /**
     * @return array
     */
    abstract public function resolve();

}