<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/17
 * Time: 16:07
 */

namespace Aw;


class Captcha
{
    private $config;
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->config = array(
            "session_name" => "captcha_session_name",
            "session_name_try_times" => "captcha_session_name_try_times",
            "session_name_try_times_num" => 4,
            "session_img_type" => "num",
            "session_img_length" => 4,
            "session_img_width" => 200,
            "session_img_height" => 50,
        );
        $this->session = $session;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
    }

    protected function setCode($code)
    {
        $this->session->set($this->config["session_name_try_times"], $this->config['session_name_try_times_num']);
        $this->session->set($this->config["session_name"], $code);
    }

    /**
     * @param $code
     * @return bool
     */
    public function check($code)
    {
        if (!isset($code)) {
            return false;
        }
        if (!$this->session->get($this->config["session_name"], false)) {
            return false;
        }
//        var_dump($this->session->get($this->config["session_name"]));
        if (is_string($code) && strlen($code) > 0) {
            $ret = $this->session->get($this->config["session_name"]) === $code;
            if (!$ret) {
                $remains = $this->session->get($this->config["session_name_try_times"]);
                $remains--;
                if ($remains <= 0) {
                    $this->session->remove($this->config["session_name"]);
                } else {
                    $this->session->set($this->config["session_name_try_times"], $remains);
                }
                return false;
            }
            $this->session->remove($this->config["session_name"]);
            return true;
        }
        return false;
    }

    //for test
//    public function _debug_get_code_238237128()
//    {
//        return $this->session->get($this->config["session_name"]);
//    }

    public function getCode_math($w, $h)
    {
        if ($w < 0) $w = $this->config["session_img_width"];
        if ($h < 0) $h = $this->config["session_img_height"];
        $im = imagecreate($w, $h);

        //imagecolorallocate($im, 14, 114, 180); // background color
        $red = imagecolorallocate($im, 255, 0, 0);
        $white = imagecolorallocate($im, 255, 255, 255);

        $num1 = rand(1, 20);
        $num2 = rand(1, 20);

        //$_SESSION['helloweba_math'] = $num1 + $num2;
        //$this->session->set($this->config["session_name"], $num1 + $num2);
        $this->setCode($num1 + $num2);
        $gray = imagecolorallocate($im, 118, 151, 199);
        $black = imagecolorallocate($im, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));

        //画背景
        imagefilledrectangle($im, 0, 0, 100, 24, $black);
        //在画布上随机生成大量点，起干扰作用;
        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($im, rand(0, $w), rand(0, $h), $gray);
        }

        imagestring($im, 5, 5, 4, $num1, $red);
        imagestring($im, 5, 30, 3, "+", $red);
        imagestring($im, 5, 45, 4, $num2, $red);
        imagestring($im, 5, 70, 3, "=", $red);
        imagestring($im, 5, 80, 2, "?", $white);

        header("Content-type: image/png");
        imagepng($im);
        imagedestroy($im);
        return true;
    }

    public function getCode_num($num, $w, $h)
    {
        if ($num < 0) $num = $this->config["session_img_length"];
        if ($w < 0) $w = $this->config["session_img_width"];
        if ($h < 0) $h = $this->config["session_img_height"];
        $code = "";
        for ($i = 0; $i < $num; $i++) {
            $code .= rand(0, 9);
        }
        //4位验证码也可以用rand(1000,9999)直接生成
        //将生成的验证码写入session，备验证页面使用
//        $this->session->set($this->config["session_name"], $code);
        $this->setCode($code);
        //创建图片，定义颜色值
        Header("Content-type: image/PNG");
        $im = imagecreate($w, $h);
        $black = imagecolorallocate($im, 0, 0, 0);
        $gray = imagecolorallocate($im, 200, 200, 200);
//        $bgcolor = imagecolorallocate($im, 255, 255, 255);

        imagefill($im, 0, 0, $gray);

        //画边框
        imagerectangle($im, 0, 0, $w - 1, $h - 1, $black);

        //随机绘制两条虚线，起干扰作用
        $style = array(
            $black,
            $black,
            $black,
            $black,
            $black,
            $gray,
            $gray,
            $gray,
            $gray,
            $gray
        );
        imagesetstyle($im, $style);
        $y1 = rand(0, $h);
        $y2 = rand(0, $h);
        $y3 = rand(0, $h);
        $y4 = rand(0, $h);
        imageline($im, 0, $y1, $w, $y3, IMG_COLOR_STYLED);
        imageline($im, 0, $y2, $w, $y4, IMG_COLOR_STYLED);

        //在画布上随机生成大量黑点，起干扰作用;
        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($im, rand(0, $w), rand(0, $h), $black);
        }
        //将数字随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
        $strx = rand(3, 8);
        for ($i = 0; $i < $num; $i++) {
            $strpos = rand(1, 6);
            imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
            $strx += rand(8, 12);
        }
        imagepng($im);
        imagedestroy($im);
        return true;
    }

    public function getCode_char($num, $w, $h)
    {
        if ($num < 0) $num = $this->config["session_img_length"];
        if ($w < 0) $w = $this->config["session_img_width"];
        if ($h < 0) $h = $this->config["session_img_height"];
        // 去掉了 0 1 O l 等
        $str = "23456789abcdefghijkmnpqrstuvwxyz";
        $code = '';
        for ($i = 0; $i < $num; $i++) {
            $code .= $str[mt_rand(0, strlen($str) - 1)];
        }
        //将生成的验证码写入session，备验证页面使用
//        $this->session->set($this->config["session_name"], $code);
        $this->setCode($code);
        //创建图片，定义颜色值
        Header("Content-type: image/PNG");
        $im = imagecreate($w, $h);
        $black = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        $gray = imagecolorallocate($im, 118, 151, 199);
        $bgcolor = imagecolorallocate($im, 235, 236, 237);

        //画背景
        imagefilledrectangle($im, 0, 0, $w, $h, $bgcolor);
        //画边框
        imagerectangle($im, 0, 0, $w - 1, $h - 1, $gray);
        //imagefill($im, 0, 0, $bgcolor);


        //在画布上随机生成大量点，起干扰作用;
        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($im, rand(0, $w), rand(0, $h), $black);
        }
        //将字符随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
        $strx = rand(3, 8);
        for ($i = 0; $i < $num; $i++) {
            $strpos = rand(1, 6);
            imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
            $strx += rand(14, 20);
        }
        imagepng($im);
        imagedestroy($im);
        return true;
    }
}