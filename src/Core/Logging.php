<?php

namespace Tethys\Core;

class Logging extends Component
{

    public $path;

    public $format = [];
    public $defaultFormat;

    protected $_currentPath;

    public function writeToFile($file, ...$data)
    {
        ob_start();
        echo date('Y-m-d H:i:s').PHP_EOL;
        $this->_prr($data);
        $this->_writeRaw($file, ob_get_clean());
    }

    public function log($file, $data)
    {
        if (isset($this->format[$file])) {
            $string = $this->_getFormattedString($this->format[$file], $data);
        } elseif ($this->defaultFormat && isset($this->format[$this->defaultFormat])) {
            $string = $this->_getFormattedString($this->format[$this->defaultFormat], $data);
        } else {
            $string = date('Y-m-d H:i:s').' '.implode(' ', $data);
        }
        $this->_writeRaw($file, $string);
    }

    /**
     * @param string $format
     * @param array ...$data
     * @return string
     */
    public function getFormattedString($format, ...$data)
    {
        return $this->_getFormattedString($format, $data);
    }

    /**
     * @param string $format
     * @param array $data
     * @return string
     */
    protected function _getFormattedString($format, $data)
    {
        return preg_replace_callback('/{(\w+)}/', function ($matches) use (&$data) {
            $param = $matches[1];
            switch ($param) {
                case('datetime'):
                    return date('Y-m-d H:i:s');
                case('date'):
                    return date('Y-m-d');
                case('time'):
                    return date('H:i:s');
                case('user'):
                    return get_current_user();
                default:
                    return strtr($data[$param], [ "\r" => '', "\n" => '\n', '"' => '\"' ]) ?? '-';
            }
        }, $format);
    }

    protected function _writeRaw($file, $raw)
    {
        try {
            $path = $this->_getPath();
            $f = fopen($path.'/'.$file.'.log', 'a');
            fwrite($f, $raw.PHP_EOL);
            fclose($f);
        } catch (\Exception $e) {
            echo 'WRITE_RAW '.$e->getMessage().PHP_EOL;
        }
    }

    protected function _getPath()
    {
        if (null === $this->_currentPath) {
            if ($this->path) {

                $this->_currentPath = preg_replace('/\/+$/', '',

                    preg_replace_callback('/\{(\w+)\}/', function ($matches) {

                    switch ($matches[1]) {
                        case('y'):
                            return date('Y');
                        case('m'):
                            return date('m');
                        case('d'):
                            return date('d');
                        default:
                            return $matches[0];
                    }

                }, $this->path));

            }
            if (!$this->_currentPath) $this->_currentPath = getcwd();
            if (!file_exists($this->_currentPath)) mkdir($this->_currentPath, 0755, true);
        }
        return $this->_currentPath;
    }

    public function prr(...$data)
    {
        $this->_prr($data);
    }

    protected function _prr($data)
    {
        foreach ($data as $datum) {
            if ($datum) {
                if (is_scalar($datum)) {
                    echo $datum.PHP_EOL;
                } else {
                    print_r($datum);
                }

            }
        }
    }

}