<?php

namespace Tethys\Web;

class Controller extends \Tethys\Core\Controller
{

    const ROBOTS_INDEX_FOLLOW = 'index,follow';
    const ROBOTS_NOINDEX_FOLLOW = 'noindex,follow';
    const ROBOTS_INDEX_NOFOLLOW = 'index,nofollow';
    const ROBOTS_NOINDEX_NOFOLLOW = 'noindex,nofollow';

    /** @var string|string[] */
    public $htmlTitle = [];

    /** @var string */
    public $htmlTitleSeparator = ' | ';

    /** @var string */
    public $robots;

    /**
     * @param array $data
     * @return string
     */
    public function renderJson(array $data)
    {
        \Tethys::response()->getHeaders()->add('Content-type', 'application/json; charset=utf-8');
        return json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    }

}