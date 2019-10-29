<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8
 * Time: 10:28
 */

namespace Aw;

class Cmd
{
    const CODE_OK = 200;
    const CODE_ERR = 500;
    const CODE_NO_CHANGE = 201;
    const CODE_NOT_FOUND = 404;
    const CODE_NO_PERMISSION = 403;
    const CODE_REDIRECT_TMP = 302;
    const CODE_REDIRECT_PERMANENT = 301;

    protected $code;
    protected $data;
    protected $message;

    public function __construct($data = array(), $message = "OK", $code = 200)
    {
        $this->ok($data, $message, $code);
    }

    /**
     * @return $this
     */
    public function markAsError()
    {
        $this->code = self::CODE_ERR;
        return $this;
    }

    /**
     * @return $this
     */
    public function markAsOk()
    {
        $this->code = self::CODE_OK;
        return $this;
    }

    /**
     * @param string $message
     * @param int $code
     * @param array $data
     * @return $this
     */
    public function error($message = "Error", $code = self::CODE_ERR, $data = array())
    {
        $this->code = $code;
        $this->data = $data;
        $this->message = $message;
        return $this;
    }

    /**
     * @param string $message
     * @param int $code
     * @param array $data
     * @return $this
     */
    public function ok($data = array(), $message = "OK", $code = 200)
    {
        $this->code = $code;
        $this->data = $data;
        $this->message = $message;
        return $this;
    }

    /**
     * @return Cmd
     */
    public function duplicate()
    {
        $n = new self();
        $n->code = $this->code;
        $n->message = $this->message;
        $n->data = $this->data;
        return $n;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function set(array $data)
    {
        if (isset($data['code'])) {
            $this->setCode($data['code']);
        }
        if (isset($data['message'])) {
            $this->setMessage($data['message']);
        }
        if (isset($data['data'])) {
            $this->setData($data['data']);
        }
        return $this;
    }

    /**
     * @param $json
     * @return $this|Cmd
     */
    public function setJson($json)
    {
        $data = @json_decode($json, true);
        if (is_array($data))
            return $this->set($data);
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        return array(
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->data
        );
    }

    /**
     * @return string
     */
    public function getJson()
    {
        return json_encode($this->get());
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return $this->code == self::CODE_OK;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return $this->code == self::CODE_ERR;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getJson();
    }
}