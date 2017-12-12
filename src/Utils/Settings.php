<?php
namespace Tethys\Utils;

use Tethys\Core\Model;

abstract class Settings extends Model
{

    /**
     * @var static
     */
    protected static $instance;

    /**
     * @return string
     *
     * Examples:
     *      json://JSON_FILE
     *      serialized://SERIALIZED_FILE
     *      ~ redis://[server[:port]/][database]key
     *
     */
    abstract public function getStorage();

    public function load()
    {
    }

    public function save()
    {
    }

    /**
     * @return static
     */
    public static function instance()
    {
        if (null === static::$instance) {
            static::$instance = static::make();
            static::$instance->load();
        }
        return static::$instance;
    }

}