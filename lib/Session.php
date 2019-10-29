<?php
/**
 * 2017/5/15 17:36:25
 * config component
 */

namespace Aw;


class Session extends Config
{
    protected $started = false;

    public function __construct($session_dir = null)
    {
        $this->setSavePath($session_dir);
    }

    public function setSavePath($session_dir)
    {
        if ($session_dir && is_dir($session_dir)) {
            session_save_path($session_dir);
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        $this->start();
        return session_id();
    }

    /**
     * @return $this
     */
    public function start()
    {
        if ($this->started)
            return $this;
        session_start();
        $this->items = &$_SESSION;
        $this->started = true;
        return $this;
    }
}
