<?php

namespace Tethys\Databases;

abstract class ViewRecord extends Record
{

    public function save()
    {
        throw new DatabaseErrorException('Can not save view record');
    }

}