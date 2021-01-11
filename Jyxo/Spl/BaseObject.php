<?php declare(strict_types = 1);

/**
 * Jyxo PHP Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/jyxo/php/blob/master/license.txt
 */

namespace Jyxo\Spl;

use function array_flip;
use function get_class_methods;
use function preg_replace;
use function ucfirst;

/**
 * Default object class.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class BaseObject implements ArrayCopy
{

	/**
	 * Returns instance class name.
	 *
	 * @return string
	 */
	final public function getClass(): string
	{
		return static::class;
	}

	/**
	 * Converts an object to an array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$values = [];

		foreach ((array) $this as $key => $value) {
			// Private and protected properties have ugly array key prefixes which we remove
			$key = preg_replace('~^.+\0~', '', $key);
			$values[$key] = $value;
		}

		return $values;
	}

	/**
	 * Returns if a class has the given method defined.
	 *
	 * @param string $class Class name
	 * @param string $method Method name
	 * @return bool
	 */
	private static function hasMethod(string $class, string $method): bool
	{
		static $cache;

		if (!isset($cache[$class])) {
			$cache[$class] = array_flip(get_class_methods($class));
		}

		return isset($cache[$class][$method]);
	}

	/**
	 * Returns a property value if it has a getter defined.
	 *
	 * @param string $name Property name
	 * @return mixed
	 */
	public function &__get(string $name)
	{
		$class = static::class;
		$name = ucfirst($name);

		// Return null if no getter is found
		$value = null;

		// Tests for possible getters
		static $types = ['get', 'is'];

		foreach ($types as $type) {
			$getter = $type . $name;

			if (self::hasMethod($class, $getter)) {
				// It's necessary to save the value to a variable first because of using &
				$value = $this->$getter();

				break;
			}
		}

		return $value;
	}

	/**
	 * Sets property value if it has a setter defined.
	 *
	 * @param string $name Propety name
	 * @param mixed $value Property value
	 */
	public function __set(string $name, $value): void
	{
		$setter = 'set' . ucfirst($name);

		if (self::hasMethod(static::class, $setter)) {
			$this->$setter($value);
		}
	}

	/**
	 * Returns if property exists. Property exists if it has defined getter.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function __isset(string $name): bool
	{
		$class = static::class;
		$name = ucfirst($name);

		// Tests for possible getters
		static $types = ['get', 'is'];

		foreach ($types as $type) {
			$getter = $type . $name;

			if (self::hasMethod($class, $getter)) {
				return true;
			}
		}

		return false;
	}

}
