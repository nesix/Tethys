<?php

namespace Tethys\Web;


use Tethys\Core\InvalidConfigException;
use Tethys\Core\Validator;
use Tethys\Utils\StrLib;


require __DIR__.'/Libs/XssFilter.php';


/**
 * Class Request
 * @package Tethys\Web
 *
 *
 *
 * SERVER
 * @property string $uri
 * @property string $url
 * @property string $protocol
 * @property string $method
 * @property string $host
 * @property string $hostInfo
 * @property string $hostName
 * @property string $port
 * @property string $securePort
 * @property string $ip
 * @property string $agent
 * @property string $browser
 * @property string $referrer
 * @property string $serverAdmin
 * @property string $authUser
 * @property string $authPassword
 * @property string $ifModifiedSince
 * @property bool   $isSecureConnection
 * @property bool   $isDevIp
 *
 * @property CookieCollection $cookies
 * @property HeaderCollection $headers
 *
 * QUERY
 * @property bool $isGet
 * @property bool $isPost
 * @property bool $isDelete
 * @property bool $isPatch
 * @property bool $isHead
 * @property bool $isAjax
 *
 * @property string $rawBody
 * @property array $queryParams
 *
 * POST
 * @property Validator $post
 *
 * GET
 * @property int $id
 * @property int $pageNumber
 *
 */

class Request extends \Tethys\Core\Request
{

    const CSRF_HEADER = 'X-CSRF-Token';

    const XSS_TAGS_STRICT = \XssFilter::TAGS_STRICT;
    const XSS_TAGS_SOFT = \XssFilter::TAGS_SOFT;
    const XSS_TAGS_NONE = \XssFilter::TAGS_NONE;

    /**
     * Включаем/выключаем CSRF валидацию
     * @var bool
     */
    public $enableCsrfValidation = true;

    /**
     * Имя токена в форме
     * @var string
     */
    public $csrfParam = '_csrf';

    /** @var string */
    private $_csrfToken;

    /** @var bool */
    public $enableCookieValidation = true;

    /** @var string */
    public $cookieValidationKey;

    /** @var string[] */
    public $developersIp = [];

    /** @var Validator */
    private $_post;

    /** @var \XssFilter */
    private $_xss;

    /**
     * @return array
     */
    public function resolve()
    {
        return \Tethys::$app->getRoutesManager()->parseRequest($this);
    }

    /**
     * @return string
     */
    protected function getUri()
    {
        $request = $_SERVER['REQUEST_URI'] ?? '';
        $uri = explode('?', $request);
        return $uri ? array_shift($uri) : $request;
    }

    private $_url;

    public function getUrl()
    {
        if ($this->_url === null) {
            $this->_url = $this->resolveRequestUri();
        }

        return $this->_url;
    }

    protected function resolveRequestUri()
    {
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            if ($requestUri !== '' && $requestUri[0] !== '/') {
                $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            throw new InvalidConfigException('Unable to determine the request URI.');
        }

        return $requestUri;
    }

    /**
     * @return string
     */
    protected function getProtocol()
    {
        return $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0';
    }

    protected function getMethod()
    {

        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            return strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            return strtoupper($_SERVER['REQUEST_METHOD']);
        }

        return 'GET';

    }

    private $_hostInfo;
    private $_hostName;

    /**
     * @return string
     */
    public function getHostInfo()
    {
        if ($this->_hostInfo === null) {
            $secure = $this->getIsSecureConnection();
            $http = $secure ? 'https' : 'http';
            if (isset($_SERVER['HTTP_HOST'])) {
                $this->_hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
            } elseif (isset($_SERVER['SERVER_NAME'])) {
                $this->_hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                $port = $secure ? $this->getSecurePort() : $this->getPort();
                if (($port !== 80 && !$secure) || ($port !== 443 && $secure)) {
                    $this->_hostInfo .= ':' . $port;
                }
            }
        }

        return $this->_hostInfo;
    }

    public function setHostInfo($value)
    {
        $this->_hostName = null;
        $this->_hostInfo = $value === null ? null : rtrim($value, '/');
    }

    public function getHostName()
    {
        if ($this->_hostName === null) {
            $this->_hostName = parse_url($this->getHostInfo(), PHP_URL_HOST);
        }

        return $this->_hostName;
    }

    private $_port;

    /**
     * Returns the port to use for insecure requests.
     * Defaults to 80, or the port specified by the server if the current
     * request is insecure.
     * @return int port number for insecure requests.
     * @see setPort()
     */
    public function getPort()
    {
        if ($this->_port === null) {
            $this->_port = !$this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 80;
        }

        return $this->_port;
    }

    /**
     * Sets the port to use for insecure requests.
     * This setter is provided in case a custom port is necessary for certain
     * server configurations.
     * @param int $value port number.
     */
    public function setPort($value)
    {
        if ($value != $this->_port) {
            $this->_port = (int) $value;
            $this->_hostInfo = null;
        }
    }

    private $_securePort;

    /**
     * Returns the port to use for secure requests.
     * Defaults to 443, or the port specified by the server if the current
     * request is secure.
     * @return int port number for secure requests.
     * @see setSecurePort()
     */
    public function getSecurePort()
    {
        if ($this->_securePort === null) {
            $this->_securePort = $this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 443;
        }

        return $this->_securePort;
    }

    /**
     * @return bool
     */
    public function getIsSecureConnection()
    {
        return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1)
            || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
    }

    /**
     * @return string
     */
    protected function getHost()
    {
        return $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
    }

    /**
     * @return string
     */
    protected function getIp()
    {
        return $_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /**
     * @param string $ip
     * @return string
     */
    public function getIsDevIp($ip = null)
    {
        if (null === $ip) $ip = $this->getIp();
        return in_array($ip, $this->developersIp);
    }

    /**
     * @return string
     */
    protected function getAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * @return string
     */
    protected function getReferrer()
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }

    /**
     * @return string
     */
    protected function getServerAdmin()
    {
        return $_SERVER['SERVER_ADMIN'] ?? '';
    }

    /**
     * @return string
     */
    protected function getAuthUser()
    {
        return $_SERVER['PHP_AUTH_USER'] ?? '';
    }

    /**
     * @return string
     */
    protected function getAuthPassword()
    {
        return $_SERVER['PHP_AUTH_PW'] ?? '';
    }

    /**
     * @return string
     */
    protected function getIfModifiedSince()
    {
        $ifModifiedSince = trim($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '');
        if (!$ifModifiedSince) return null;
        if (!is_numeric($ifModifiedSince)) $ifModifiedSince = strtotime($ifModifiedSince);
        return $ifModifiedSince ? date('Y-m-d H:i:s', $ifModifiedSince) : null;
//        $ifModifiedSince = trim($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '');
//        return $ifModifiedSince ? StrLib::gmtDate($ifModifiedSince) : null;
    }

    protected function getIsGet()
    {
        return 'GET' === $this->getMethod();
    }

    protected function getIsPost()
    {
        return 'POST' === $this->getMethod();
    }

    protected function getIsDelete()
    {
        return 'DELETE' === $this->getMethod();
    }

    protected function getIsPatch()
    {
        return 'PATCH' === $this->getMethod();
    }

    protected function getIsHead()
    {
        return 'HEAD' === $this->getMethod();
    }

    protected function getIsAjax()
    {
        return 'XMLHttpRequest' === ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
    }

    private $_rawBody;

    /**
     * @return string the request body
     */
    protected function getRawBody()
    {
        if ($this->_rawBody === null) {
            $this->_rawBody = file_get_contents('php://input');
        }

        return $this->_rawBody;
    }

    /**
     * @param string $rawBody the request body
     */
    protected function setRawBody($rawBody)
    {
        $this->_rawBody = $rawBody;
    }

    /**
     * @return Validator
     */
    public function getPost()
    {
        if (null === $this->_post && $this->isPost) {

            $data = $this->xssFilter($_POST ?: [], self::XSS_TAGS_NONE);

            /** @var Validator $class */
            $class = $this->validatorClass;
            $this->_post = $class::make($data);

        }
        return $this->_post;
    }

    private $_queryParams;

    protected function getQueryParams()
    {
        if (null === $this->_queryParams) {
            $this->_queryParams = $_GET ?: [];
            $this->_queryParams && $this->xssFilter($this->_queryParams);
        }
        return $this->_queryParams;
    }

    protected function setQueryParams($values)
    {
        $this->_queryParams = $values;
    }

    /**
     * @return int|null
     */
    protected function getId()
    {
        $id = (int)($this->getQueryParams()['id'] ?? 0);
        return $id ?: null;
    }

    /**
     * @return int
     */
    protected function getPageNumber()
    {
        $page = (int)($this->getQueryParams()['page'] ?? 0);
        return $page ?: 1;
    }

    /** @var CookieCollection */
    private $_cookies;

    /**
     * @return CookieCollection
     */
    protected function getCookies()
    {
        if (null === $this->_cookies) {
            $this->_cookies = CookieCollection::create($this->loadCookies(), [
                'readOnly' => true,
            ]);
        }
        return $this->_cookies;
    }

    /** @var HeaderCollection */
    private $_headers;

    public function getHeaders()
    {
        if ($this->_headers === null) {
            $this->_headers = HeaderCollection::make();
            if (function_exists('getallheaders')) {
                $headers = getallheaders();
            } elseif (function_exists('http_get_request_headers')) {
                $headers = http_get_request_headers();
            } else {
                foreach ($_SERVER as $name => $value) {
                    if (strncmp($name, 'HTTP_', 5) === 0) {
                        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                        $this->_headers->add($name, $value);
                    }
                }
                return $this->_headers;
            }
            foreach ($headers as $name => $value) {
                $this->_headers->add($name, $value);
            }
        }

        return $this->_headers;
    }

    public function getBrowser()
    {
        $agent = $this->getAgent();
        if (preg_match('#(?<browser>MSIE|Opera|Firefox|Chrome|Version|Opera Mini|Netscape|Konqueror|SeaMonkey|Camino|Minefield|Iceweasel|K-Meleon|Maxthon|AsyncHttpClient)(?:\/| )(?<version>[0-9.]+)#', $agent, $res)) {
            if (preg_match("/Opera ([0-9.]+)/i", $agent, $opera)) {
                // определение _очень_старых_ версий Оперы (до 8.50), при желании можно убрать
                return [ 'name' => 'Opera', 'version' => $opera[1] ];
            } else {
                $ret = [ 'name' => $res['browser'], 'version' => $res['version'] ];
                switch ($res['browser']) {
                    case('MSIE'): // если браузер определён как IE
                        if (preg_match("/(Maxthon|Avant Browser|MyIE2)/i", $agent, $ie)) { // проверяем, не разработка ли это на основе IE
                            // если да, то возвращаем сообщение об этом
                            $ret['name'] = $ie[1].' based on IE';
                        }
                        break;
                    case('Firefox'): // если браузер определён как Firefox
                        // проверяем, не разработка ли это на основе Firefox
                        if (preg_match('/(Flock|Navigator|Epiphany)\/([0-9.]+)/', $agent, $ff)) {
                            // если да, то выводим номер и версию
                            $ret = [ 'name' => $ff[1],'version' => $ff[2]];
                        }
                        break;
                    case('Opera'):
                        // если браузер определён как Opera 9.80, берём версию Оперы из конца строки
                        if ('9.80' == $res['version']) $ret['version'] = substr($agent, -5);
                        break;
                    case('Version'):
                        $ret['name'] = 'Safari';
                        break;
                }
                return $ret;
            }
        } elseif (preg_match('#gecko#i', $agent)) {
            return [ 'name' => 'Gecko', 'version' => $agent];
        }
        return [ 'name' => 'Unknown', 'version' => '' ];
    }

    /**
     * @return Cookie[]
     * @throws InvalidConfigException
     */
    protected function loadCookies()
    {
        $cookies = [];
        if ($this->enableCookieValidation) {
            if (!$this->cookieValidationKey) {
                throw new InvalidConfigException(get_class($this) . '::cookieValidationKey must be configured with a secret key.');
            }
            foreach ($_COOKIE as $name => $value) {
                if (!is_string($value)) {
                    continue;
                }
                $data = \Tethys::security()->validateData($value, $this->cookieValidationKey);
                if ($data === false) {
                    continue;
                }
                $data = @unserialize($data);
                if (is_array($data) && isset($data[0], $data[1]) && $data[0] === $name) {
                    $cookies[$name] = new Cookie([
                        'name' => $name,
                        'value' => $data[1],
                        'expire' => null,
                    ]);
                }
            }
        } else {
            foreach ($_COOKIE as $name => $value) {
                $cookies[$name] = new Cookie([
                    'name' => $name,
                    'value' => $value,
                    'expire' => null,
                ]);
            }
        }

        return $cookies;
    }



    /**
     * @param mixed $data
     * @param string $tags
     * @return mixed
     */
    public function xssFilter($data, $tags = null)
    {
        if (null === $this->_xss) $this->_xss = new \XssFilter();
        return $this->_xss->filter($data, $tags);
    }

    /**
     * CSRF
     */

    /**
     * @return null|string
     */
    public function getCsrfTokenFromCookies()
    {
        return $this->getCookies()->getValue($this->csrfParam);
    }

    /**
     * @return mixed
     */
    public function getCsrfTokenFromHeader()
    {
        return $this->headers[self::CSRF_HEADER];
    }

    /**
     * @param bool $regenerate
     * @return string
     */
    public function getCsrfToken($regenerate = false)
    {
        if ($this->_csrfToken === null || $regenerate) {
            if ($regenerate || null === ($token = $this->getCsrfTokenFromCookies())) {
                $token = $this->generateCsrfToken();
            }
            $this->_csrfToken = \Tethys::security()->maskToken($token);
        }

        return $this->_csrfToken;
    }

    protected function generateCsrfToken()
    {
        $token = \Tethys::security()->generateRandomKey();

        /** @var Response $response */
        $response = \Tethys::response();
        $response->getCookies()->add(Cookie::make([
            'name' => $this->csrfParam,
            'value' => $token,
            'httpOnly' => true
        ]));

        return $token;
    }

    public function validateCsrfToken($clientSuppliedToken = null)
    {
        if (!$this->enableCsrfValidation || in_array($this->method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        $trueToken = $this->getCsrfToken();

        if (null !== $clientSuppliedToken) {
            return $this->validateCsrfTokenInternal($clientSuppliedToken, $trueToken);
        }

        return $this->validateCsrfTokenInternal($this->post[$this->csrfParam], $trueToken)
            || $this->validateCsrfTokenInternal($this->getCsrfTokenFromHeader(), $trueToken);

    }

    private function validateCsrfTokenInternal($clientSuppliedToken, $trueToken)
    {
        if (!is_string($clientSuppliedToken)) {
            return false;
        }
        $security = \Tethys::security();
        return $security->unmaskToken($clientSuppliedToken) === $security->unmaskToken($trueToken);
    }

}