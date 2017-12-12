<?php

namespace Tethys\Forms;

use Tethys\Web\CookieCollection;
use Tethys\Web\RedirectException;

class FormReloadException extends RedirectException
{

    public function __construct(array $errors, $message = null, $code = 0, \Exception $previous = null)
    {
        /** @var CookieCollection $cookies */
        $cookies = \Tethys::$app->get('cookies');



        //Cookies::set('_form_errors', serialize($errors));
        parent::__construct('', false, $message, $code, $previous);
    }

    public function getName()
    {
        return 'Reload form';
    }

}