<?php

namespace Aw\Cache;

use Aw\Filesystem\Filesystem;
use Exception;

class FileCache implements ICache
{
    /**
     * 缓存所在目录
     * @var string $path
     */
    protected $path;
    // 连接

    /**
     * FileCache constructor.
     * @param $dir
     * @throws Exception
     */
    public function __construct($dir)
    {
        if (!file_exists($dir)) {
            throw new Exception($dir . ' is not exists');
        }
        $this->path = $dir;
    }

    protected function getFileCachePath($name)
    {
        return $this->path . '/' . md5($name) . ".php";
    }

    /**
     * @param $name
     * @param $value
     * @param int $expire 单位秒
     * @return bool|int|mixed
     */
    public function set($name, $value, $expire = 0)
    {
        $file = $this->getFileCachePath($name);
        $expire = sprintf("%010d", $expire > 0 ? $expire + time() : 0);
        $data = $expire . serialize($value);
        return file_put_contents($file, $data);
    }

    /**
     * @param $name
     * @return bool|mixed|null
     */
    public function get($name)
    {
        $file = $this->getFileCachePath($name);
        //缓存文件不存在
        if (!is_file($file) || !is_readable($file)) {
            return null;
        }
        $content = file_get_contents($file);
        $expire = intval(substr($content, 0, 10));

        //缓存失效处理
        if ($expire > 0 && $expire < time()) {
            @unlink($file);
            return false;
        }
        return unserialize(substr($content, 10));
    }

    /**
     * 删除
     * @param $name
     * @return bool|mixed
     */
    public function del($name)
    {
        return FileSystem::delFile($this->getFileCachePath($name));
    }

    /**
     * 删除缓存池
     * @return bool|mixed
     */
    public function flush()
    {
        return Filesystem::delDir($this->path, false);
    }
}