<?php

namespace Tethys\Core;

class Response extends Component
{

    public $exitStatus = 0;

    public $result;

    public function send()
    {
        echo $this->result;
    }

    /**
     * Removes all existing output buffers.
     */
    public function clearOutputBuffers()
    {
        for ($level = ob_get_level(); $level > 0; --$level) {
            if (!@ob_end_clean()) {
                ob_clean();
            }
        }
    }

}