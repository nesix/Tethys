<?php

namespace Tethys\Core;

abstract class Model extends Component
{

    const FIELD_ID = 'id';
    const FIELD_INT = 'int';
    const FIELD_FLOAT = 'float';
    const FIELD_STRING = 'string';
    const FIELD_DATE = 'date';
    const FIELD_DATETIME = 'datetime';
    const FIELD_TIME = 'time';
    const FIELD_BOOL = 'bool';
    const FIELD_EMAIL = 'email';
    const FIELD_IP = 'ip';
    const FIELD_IPv6 = 'ip6';
    const FIELD_ALIAS = 'alias';
    const FIELD_GUID = 'guid';

    abstract public function save();

    public function validate()
    {
        $errors = [];

        /** @var ModelValidator[] $validators */
        $validators = static::getValidators();

        foreach (static::getFields() as $fieldName => $fieldParams) {

            $type = $fieldParams[0] ?? '';
            if (!$type) throw new Exception(); // todo: make field type exception

            if (isset($validators[$type])) {
                try {
                    $validators[$type]::validate($this, $fieldName, $fieldParams);
                } catch (ModelFieldErrorException $e) {
                    $errors[$fieldName] = $e->getMessage();
                }
            }
        }

        return $errors;
    }

    public static function getFields()
    {
        return [];
    }

    public static function getValidators()
    {
        return [
            static::FIELD_ID => 'Tethys\Core\ModelValidator',
            static::FIELD_INT => 'Tethys\Core\ModelValidator',
            static::FIELD_FLOAT => 'Tethys\Core\ModelValidator',
            static::FIELD_STRING => 'Tethys\Core\ModelValidator',
            static::FIELD_DATE => 'Tethys\Core\ModelValidator',
            static::FIELD_DATETIME => 'Tethys\Core\ModelValidator',
            static::FIELD_TIME => 'Tethys\Core\ModelValidator',
            static::FIELD_BOOL => 'Tethys\Core\ModelValidator',
            static::FIELD_EMAIL => 'Tethys\Core\ModelValidator',
            static::FIELD_IP => 'Tethys\Core\ModelValidator',
        ];
    }

}