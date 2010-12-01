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
 * Default object class.
 *
 * @category Jyxo
 * @package Jyxo\Spl
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class Object implements \Jyxo\Spl\ArrayCopy
{
	/**
	 * Returns instances class name.
	 *
	 * @return string
	 */
	public final function getClass()
	{
		return get_class($this);
	}

	/**
	 * Returns property if it has defined getter.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		$class = get_class($this);
		$name = ucfirst($name);

		// If getter doesn't exist, return null
		$value = null;

		// Tests for possible getters
		static $types = array('get', 'is');
		foreach ($types as $type) {
			$getter = $type . $name;
			if (self::hasMethod($class, $getter)) {
				// It's necessary to save it to variable first because of &
				$value = $this->$getter();
				break;
			}
		}

		return $value;
	}

	/**
	 * Sets property if it has defined setter.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$setter = 'set' . ucfirst($name);
		if (self::hasMethod(get_class($this), $setter)) {
			$this->$setter($value);
		}
	}

	/**
	 * Returns if property exists. Property exists if it has defined getter.
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		$class = get_class($this);
		$name = ucfirst($name);

		// Otestuje možné gettery
		static $types = array('get', 'is');
		foreach ($types as $type) {
			$getter = $type . $name;
			if (self::hasMethod($class, $getter)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns if class has defined method.
	 *
	 * @param string $class
	 * @param string $method
	 * @return boolean
	 */
	private static function hasMethod($class, $method)
	{
		static $cache;
		if (!isset($cache[$class])) {
			$cache[$class] = array_flip(get_class_methods($class));
		}
		return isset($cache[$class][$method]);
	}

	/**
	 * Converts object to array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$values = array();
		foreach ((array) $this as $key => $value) {
			// private and protected properties have ugly array key prefixes which we remove
			$key = preg_replace('~^.+\0~', '', $key);
			$values[$key] = $value;
		}
		return $values;
	} // toArray();
}
