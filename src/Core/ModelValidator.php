<?php

namespace Tethys\Core;

class ModelValidator extends BaseObject
{

    public $fieldName;
    public $fieldParams;
    public $fieldValue;

    private static $defaultValidator;

    private static $validateFunctions = [
        Model::FIELD_ID => '_checkId',
        Model::FIELD_INT => '_checkInt',
        Model::FIELD_FLOAT => '_checkFloat',
        Model::FIELD_STRING => '_checkString',
        Model::FIELD_DATE => '_checkDate',
        Model::FIELD_DATETIME => '_checkDatetime',
        Model::FIELD_TIME => '_checkTime',
        Model::FIELD_BOOL => '_checkBool',
        Model::FIELD_EMAIL => '_checkEmail',
        Model::FIELD_IP => '_checkIp4',
        Model::FIELD_IPv6 => '_checkIp6',
    ];

    /**
     * @param Model $model
     * @param string $fieldName
     * @param array $fieldParams
     * @throws Exception
     */
    public static function validate($model, $fieldName, $fieldParams)
    {
        $value = $model->$fieldName;

        $important = !!($fieldParams['important'] ?? 0);

        if ($important && !$value) throw new ModelFieldErrorException('Это поле является обязательным');

        if (null !== $value) {

            $type = $fieldParams[0] ?? '';
            if (!$type) throw new ModelErrorException('Не указан тип поля');
            if (!isset(self::$validateFunctions[$type])) throw new ModelErrorException('Для данного типа поля не определена функция валидации ~'.$fieldName.':'.($type?:'empty_type').'~');

            if (null === self::$defaultValidator) self::$defaultValidator = self::make();

            self::$defaultValidator->obtain([
                'fieldName' => $fieldName,
                'fieldParams' => $fieldParams,
                'fieldValue' => $value,
            ]);

            self::$defaultValidator->{self::$validateFunctions[$type]}();

        }
    }

    /**
     * @throws Exception
     */
    protected function _checkInt()
    {
    }

    /**
     * @throws Exception
     */
    protected function _checkId()
    {

    }

    /**
     * @throws Exception
     */
    protected function _checkFloat()
    {
    }

    /**
     * @throws Exception
     */
    protected function _checkString()
    {
    }

    /**
     * @throws Exception
     */
    protected function _checkDate()
    {
    }

    /**
     * @throws Exception
     */
    protected function _checkDatetime()
    {
    }

    /**
     * @throws Exception
     */
    protected function _checkTime()
    {
    }

    /**
     * @throws Exception
     */
    protected function _checkBool()
    {
    }

    /**
     * @throws Exception
     */
    protected function _checkEmail()
    {
        $validator = \Tethys::request()->getValidatorClass();
        if (!$validator::checkEmail($this->fieldValue)) throw new ModelFieldErrorException('Почтовый ящик указан некорректно');
    }

    /**
     * @throws Exception
     */
    protected function _checkIp4()
    {
        if (!preg_match('^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$', $this->fieldValue)) {
            throw new ModelFieldErrorException('IP адрес указан некорректно');
        }
    }

    /**
     * @throws Exception
     */
    protected function _checkIp6()
    {
    }

}