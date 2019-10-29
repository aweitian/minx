<?php

namespace Aw\Cache;

class Redis implements ICache {
	protected $link;
	/**
	 *
	 * @param array $conf        	
	 * @throws \Exception
	 */
	public function __construct(array $conf) {
		$this->link = new \Redis ();
		if (! $this->link->connect ( $conf ['host'], $conf ['port'] )) {
			throw new \Exception ( "Redis 连接失败:" . $this->link->getLastError () );
		}
		$this->link->auth ( $conf ['password'] );
		$this->link->select ( $conf ['database'] );
	}
	// 设置
	public function set($name, $value, $expire = 3600) {
		if ($this->link->set ( $name, $value )) {
			return $this->link->expire ( $name, $expire );
		}
	}
	// 获得
	public function get($name) {
		return $this->link->get ( $name );
	}
	// 删除
	public function del($name) {
		return $this->link->del ( $name );
	}
	// 清除缓存
	public function flush() {
		return $this->link->flushall ();
	}
}