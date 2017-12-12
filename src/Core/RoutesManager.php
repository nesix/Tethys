<?php

namespace Tethys\Core;

abstract class RoutesManager extends Component
{

    /**
     * @param Request $request
     * @return array
     */
    abstract public function parseRequest($request);

}