<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aw\Http;

class Request
{
    /**
     * 在匹配,通过管道时设置运算结果提供给后面使用
     * @var array
     */
    public $carry = array();
    public $defaultUa = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';
    protected $scheme;
    protected $host;
    protected $port;
    protected $isJsonAccept;


    protected $userAgent;
    protected $path;
    protected $query = array();
    protected $method;
    protected $content;

    public function __construct($path = '', $method = '')
    {
        $this->initFromGlobal($path, $method);
    }

    /**
     * @return mixed
     */
    public function isJsonAccept()
    {
        return $this->isJsonAccept;
    }

    /**
     * @param mixed $isJsonAccept
     * @return Request
     */
    public function setJsonAccept($isJsonAccept)
    {
        $this->isJsonAccept = $isJsonAccept;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @param mixed $scheme
     * @return Request
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     * @return Request
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param mixed $userAgent
     * @return Request
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return Request
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param array $query
     * @return Request
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     * @return Request
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     * @return Request
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }


    protected function initFromGlobal($path, $method)
    {
        if ($path) {
            $this->path = $path;
        } else {
            if (isset($_SERVER['PATH_INFO'])) {
                $this->path =  $_SERVER['PATH_INFO'];
            } else {
                $r = explode('?', $this->requestUri(), 2);
                if (count($r) == 2) {
                    $this->path = $r[0];
                } else {
                    $this->path = $this->requestUri();
                }
            }
        }
        if ($method) {
            $this->setMethod($method);
        } else if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->method = $_SERVER['REQUEST_METHOD'];
        } else {
            $this->method = 'GET';
        }
        $this->scheme = $this->isHttps() ? 'https' : 'http';
        $this->port = $this->getPort();
        $this->host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : '127.0.0.1';
        $this->query = isset($_GET) ? $_GET : array();
        $this->content = file_get_contents('php://input');
        $this->isJsonAccept = $this->_isJsonAccept();
        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : $this->defaultUa;
    }

    /**
     * http://localhost/we/werw?wer=fwer#sdfsd => /we/werw?wer=fwer
     * @return string
     */
    protected function requestUri()
    {
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $uri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $uri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            $uri = '';
        }
        return $uri;
    }

    protected function isHttps()
    {
        if (isset ($_SERVER ['HTTPS']) && $_SERVER ['HTTPS'] == 'on')
            return true;
        return false;
    }

    protected function getPort()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $arr = explode(':', $_SERVER['HTTP_HOST']);
            $count = count($arr);
            if ($count > 1) {
                $port = intval($arr[$count - 1]);
                return $port;
            }
        }
        if (isset($_SERVER['SERVER_PORT'])) {
            return intval($_SERVER['SERVER_PORT']);
        } else {
            if ($this->isHttps()) {
                return 443;
            }
            return 80;
        }
    }

    protected function _isJsonAccept(){
        if(!isset($_SERVER["HTTP_ACCEPT"]))return false;
        return false !== strpos($_SERVER["HTTP_ACCEPT"], "application/json");
    }
}
