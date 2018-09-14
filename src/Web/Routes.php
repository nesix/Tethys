<?php

namespace Tethys\Web;

use Tethys\Core\Component;

class Routes extends Component
{

    public $rules;

    /**
     * @param Request $request
     * @return array|bool
     * @throws \Exception
     */
    public function parseRequest(Request $request)
    {
        if (!is_array($this->rules)) return false;

        foreach ($this->rules as $reg=>$params) {

            if (preg_match('#^'.$reg.'$#u', $request->uri, $matches)) {

                $defParams = [];
                foreach ($matches as $i=>$match) {
                    if (!is_numeric($i)) $defParams[$i] = $match;
                }

                $replace = function ($text) use (&$matches)
                {
                    return preg_replace_callback('#\{(.+?)\}#', function ($f) use ($matches) {
                        return $matches[$f[1]] ?? '';
                    }, $text);
                };

                if (is_array($params)) {

                    if (!$params) throw new BadRouteHttpException();

                    foreach ($params as $f=>$v) {
                        if (is_string($v)) $params[$f] = $replace($v);
                    }

                    $route = array_shift($params);

                    $ret = [];
                    $words = explode('-', $route);
                    $ret[] = array_shift($words);
                    foreach ($words as $word) $ret[] = ucfirst($word);

                    return [ implode($ret), array_merge($defParams, $params) ];

                } elseif (is_string($params)) {

                    throw new RedirectException($replace($params), true);

                } elseif (is_callable($params)) {

                    call_user_func_array($params, $defParams);
                    exit;

                }

            }
        }
        return false;
    }

}