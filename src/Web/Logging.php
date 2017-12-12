<?php

namespace Tethys\Web;

class Logging extends \Tethys\Core\Logging
{

    public function prr(...$data)
    {
        ob_start();
        ob_implicit_flush(false);
        $this->_prr($data);
        $result = ob_get_clean();
        if ($result) echo "<pre>\n".$result."</pre>\n";
    }

}