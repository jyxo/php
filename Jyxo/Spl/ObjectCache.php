<?php

/**
 * Jyxo Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/jyxo/php/blob/master/license.txt
 */

namespace Jyxo\Spl;

/**
 * Simple object cache so we don't have to create them or write caching over again.
 *
 * <?php
 * // Example code
 * <code>
 * 	$key = 'User_Friends/' . $user->username();
 * 	return \Jyxo\Spl\ObjectCache::get($key) ?: \Jyxo\Spl\ObjectCache::set($key, new User_Friends($this->context, $user));
 * </code>
 *
 * @category Jyxo
 * @package Jyxo\Spl
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek <libs@jyxo.com>
 */
class ObjectCache
{
	/**
	 * Object storage
	 *
	 * @var array
	 */
	private $storage = array();

	/**
	 * Returns default storage for static access
	 *
	 * @return \Jyxo\Spl\ObjectCache
	 */
	public static function getInstance()
	{
		static $instance = null;
		if (null === $instance) {
			$instance = new self();
		}
		return $instance;
	} // getInstance();

	/**
	 * Returns object from default storage
	 *
	 * @param string $key
	 * @return object
	 */
	public static function get($key)
	{
		return self::getInstance()->$key;
	} // get();

	/**
	 * Clear whole storage
	 *
	 * @return \Jyxo\Spl\ObjectCache
	 */
	public function clear()
	{
		$this->storage = array();
		return $this;
	} // clear();

	/**
	 * Saves object do default storage
	 *
	 * @param string $key
	 * @param object $value
	 * @return object saved object
	 */
	public static function set($key, $value)
	{
		self::getInstance()->$key = $value;
		return $value;
	} // set();

	/**
	 * Returns object from own storage
	 *
	 * @param string $key
	 * @return object
	 */
	public function __get($key)
	{
		return isset($this->storage[$key]) ? $this->storage[$key] : null;
	} // __get();

	/**
	 * Saves object to own storage
	 *
	 * @param string $key
	 * @param object $value
	 */
	public function __set($key, $value)
	{
		$this->storage[$key] = $value;
	} // __set();

	/**
	 * Returns if there's object with key $key in storage
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		return isset($this->storage[$key]);
	} // __isset();
}
