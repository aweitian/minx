<?php
namespace Aw\Cache;

interface ICache
{
	/**
	 * @param $name
	 * @param $value
	 * @param $expire
	 *
	 * @return mixed
	 */
	public function set($name, $value, $expire);
	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function get($name);
	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function del($name);
	/**
	 * @return mixed
	 */
	public function flush();
}