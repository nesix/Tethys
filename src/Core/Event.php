<?php

namespace Tethys\Core;

class Event extends BaseObject
{

    /**
     * @var string
     */
    public $name;

    /**
     * @var Component
     */
    public $sender;

    /**
     * @var bool
     */
    public $prevented = false;

    /**
     * @var mixed
     */
    public $data;

}