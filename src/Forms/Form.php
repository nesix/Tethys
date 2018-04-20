<?php

namespace Tethys\Forms;

use Tethys\Core\Component;
use Tethys\Core\Model;
use Tethys\Core\ModelFieldErrorException;
use Tethys\Web\RedirectException;
use Tethys\Web\Request;

/**
 * @method string(string $name, array $options = [], bool $direct = false)
 * @method text(string $name, array $options = [], bool $direct = false)
 * @method check(string $name, array $options = [], bool $direct = false)
 * @method radio(string $name, array $options = [], bool $direct = false)
 * @method hidden(string $name, array $options = [], bool $direct = false)
 * @method password(string $name, array $options = [], bool $direct = false)
 * @method number(string $name, array $options = [], bool $direct = false)
 *
 * @property array $reloadData
 * @property array $errors
 * @property array $post
 *
 */
class Form extends Component
{

    protected $field_id_prefix;

    /** @var int */
    public $index;

    /** @var Model */
    public $model;

    /** @var string */
    public $prefix;

    private static $INDEX = 0;

    private $view;

    private $modelFields;

    public function csrfInsert()
    {
        /** @var Request $request */
        $request = \Tethys::request();
        return $this->hidden($request->csrfParam, [ 'primaryValue' => $request->getCsrfToken(true) ], true);
    }

    public function csrfValidate($errorMessage = '')
    {
        /** @var Request $request */
        $request = \Tethys::request();
        if (!$request->validateCsrfToken()) {
            $this->reload($errorMessage ? [ 'csrf' => $errorMessage ] : []);
        }
    }

    public function __call($name, $arguments)
    {
        if (!isset(static::getTemplates()[$name])) throw new FormRenderErrorException('Template for ~'.$name.'~ not defined');
        $template = static::getTemplates()[$name];
        if (!file_exists($template)) throw new FormRenderErrorException('Template ~'.$template.'~ not found');

        $fieldName = $arguments ? array_shift($arguments) : '';
        if (('submit' !== $name) && !$fieldName) throw new FormRenderErrorException('Expected field name');

        $fieldArguments = $arguments ? array_shift($arguments) : [];
        $direct = $arguments ? array_shift($arguments) : 0;

        $data = $fieldArguments;

        if ($direct) {
            $data = array_merge($fieldArguments, [
                'id' => ($this->field_id_prefix?$this->field_id_prefix.'_':'').($this->prefix ? ( $this->prefix.'_'.$fieldName ) : $fieldName).( $this->index > 1 ? '_'.$this->index : ''),
                'name' => $fieldName,
                'value' => $fieldArguments['primaryValue'] ?? ($this->post[$fieldName] ?? ( $fieldArguments['value'] ?? null )),
                'error' => $this->errors[$fieldName] ?? '',
            ]);
//            $data['name'] = $fieldName;
            return $this->view()->renderFile($template, $data, $this);
        }

        list($fieldType, $fieldParams) = $this->getField($fieldName);

        $data = array_merge($fieldParams, $data);

        try {

            $data = array_merge($data, [
                'id' => ($this->field_id_prefix?$this->field_id_prefix.'_':'').($this->prefix ? ( $this->prefix.'_'.$fieldName ) : $fieldName).( $this->index > 1 ? '_'.$this->index : ''),
                'type' => $fieldType,
                'name' => $this->prefix ? ( $this->prefix.'['.$fieldName.']' ) : $fieldName,
                'value' => $this->post[$fieldName] ?? $this->model->$fieldName,
                'error' => $this->errors[$fieldName] ?? '',
            ]);

        } catch (ModelFieldErrorException $e) {
            throw new FormRenderErrorException($e->getMessage());
        }

        return $this->view()->renderFile($template, $data, $this);
    }

    public function getField($name)
    {
        if (null === $this->modelFields) {
            $this->modelFields = $this->model::getFields();
        }
        if (!isset($this->modelFields[$name])) throw new FormRenderErrorException('Unknown model field ~'.$name.'~');
        $params = $this->modelFields[$name];
        $fieldType = $params ? array_shift($params) : '';
        return [ $fieldType, $params ];
    }

    public function view()
    {
        if (null === $this->view) $this->view = \Tethys::view();
        return $this->view;
    }

    public function reload($errors)
    {
        /** @var Request $request */
        $request = \Tethys::request();
        $this->reloadData = [
            'errors' => $errors,
            'post' => $request->post->sourceData,
        ];
        throw new RedirectException();
    }

    private static $_reloadData;

    protected function setReloadData($value)
    {
        $_SESSION['RELOAD_DATA'] = self::$_reloadData = serialize($value);
    }

    protected function getReloadData()
    {
        if (null === self::$_reloadData ) {
            if (isset($_SESSION['RELOAD_DATA'])) {
                static::$_reloadData = unserialize($_SESSION['RELOAD_DATA']) ?: null;
                unset($_SESSION['RELOAD_DATA']);
            }
        }
        return self::$_reloadData;
    }

    public function getErrors()
    {
        return $this->reloadData['errors'] ?: [];
    }

    public function getPost($fieldName = '')
    {
        $post = $this->reloadData['post'] ?: [];
        return $fieldName ? ($post[$fieldName] ?? null) : $post;
    }

    public static function getTemplates()
    {
        $baseDir = __DIR__.'/Templates';
        return [
            'string' => $baseDir.'/string.php',
            'text' => $baseDir.'/text.php',
            'check' => $baseDir.'/check.php',
            'radio' => $baseDir.'/radio.php',
            'hidden' => $baseDir.'/hidden.php',
            'password' => $baseDir.'/password.php',
            'number' => $baseDir.'/number.php',
        ];
    }

    public static function model(Model $model, $prefix = '')
    {
        return static::make([
            'index' => ++self::$INDEX,
            'model' => $model,
            'prefix' => $prefix,
        ]);
    }

}