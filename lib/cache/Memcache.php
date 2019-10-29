<?php

namespace Aw\Cache;

class Memcache implements ICache {
	protected $link;
	// 连接
	public function __construct(array $conf) {
		if ($this->link = new \Memcache ()) {
			$this->link->addServer ( $conf ['host'], $conf ['port'] );
		} else {
			throw new \Exception ( "Memcache 连接失败" );
		}
	}
	// 设置
	public function set($name, $value, $expire = 0) {
		return $this->link->set ( $name, $value, 0, $expire );
	}
	// 获得
	public function get($name) {
		return $this->link->get ( $name );
	}
	// 删除
	public function del($name) {
		return $this->link->delete ( $name );
	}
	// 删除缓存池
	public function flush() {
		return $this->link->flush ();
	}
}