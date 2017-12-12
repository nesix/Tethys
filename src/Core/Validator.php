<?php

namespace Tethys\Core;

/**
 * Class Validator
 * @property mixed $sourceData
 */
class Validator extends Component implements \ArrayAccess
{

    public static $REG_EMAIL = '/^[a-z0-9!#$%&*+-=?^_`{|}~]+(\.[a-z0-9!#$%&*+-=?^_`{|}~]+)*@([-a-z0-9]+\.)+([a-z]{2,3}|info|name)$/ix';

    protected $data;

    /**
     * @param string $name
     * @param int|null $defaultValue
     * @return int|null
     */
    public function int($name, $defaultValue = null)
    {
        $value = $this[$name];
        return (null !== $value) && is_numeric($value)
            ? intval($value)
            : $defaultValue;
    }

    /**
     * @param string $name
     * @param float|null $defaultValue
     * @return float|null
     */
    public function float($name, $defaultValue = null)
    {
        $value = $this[$name];
        $stringValue = null === $value ? '' : $value;

        if ($stringValue) {

            $dotPos = mb_strrpos($stringValue, '.');
            $commaPos = mb_strrpos($stringValue, ',');
            $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
                ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

            if (!$sep) {
                return floatval(preg_replace("/[^0-9]/", '', $stringValue));
            }

            return floatval(preg_replace("/[^0-9]/", '', mb_substr($stringValue, 0, $sep)) . '.' .
                preg_replace("/[^0-9]/", '', mb_substr($stringValue, $sep+1)));

        }

        return $defaultValue;
    }

    /**
     * @param string $name
     * @param string|null $defaultValue
     * @return string|null
     */
    public function text($name, $defaultValue = null)
    {
        return $this[$name] ?: $defaultValue;
    }

    /**
     * @return array
     */
    public function getSourceData()
    {
        return $this->data;
    }

    /**
     * Simple email validator
     * @param string $address
     * @return bool
     */
    public static function checkEmail($address)
    {
        return $address && preg_match(static::$REG_EMAIL, $address);
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return (isset($this->data[$offset]) ? strval(trim($this->data[$offset])) : null) ?: null;
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (null === $this->data) $this->data = [];
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if (isset($this->data[$offset])) unset($this->data[$offset]);
    }

    /**
     * @param array $row
     * @return static|BaseObject
     */
    public static function make(array $row = [])
    {
        return parent::make([
            'data' => $row
        ]);
    }

}