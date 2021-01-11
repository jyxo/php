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

use ArrayIterator;
use IteratorAggregate;

/**
 * Simple object cache so we don't have to create them or write caching over again.
 *
 * Example:
 * <code>
 * $key = 'User_Friends/' . $user->username();
 * return \Jyxo\Spl\ObjectCache::get($key) ?: \Jyxo\Spl\ObjectCache::set($key, new \User\Friends($this->context, $user));
 * </code>
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class ObjectCache implements IteratorAggregate
{

	/**
	 * Object storage.
	 *
	 * @var array
	 */
	private $storage = [];

	/**
	 * Returns default storage for static access.
	 *
	 * @return ObjectCache
	 */
	public static function getInstance(): self
	{
		static $instance = null;

		if ($instance === null) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Clear the whole storage.
	 *
	 * @return ObjectCache
	 */
	public function clear(): self
	{
		$this->storage = [];

		return $this;
	}

	/**
	 * Returns an iterator.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->storage);
	}

	/**
	 * Returns an object from the default storage.
	 *
	 * @param string $key Object key
	 * @return object
	 */
	public static function get(string $key)
	{
		return self::getInstance()->$key;
	}

	/**
	 * Saves an object into the default storage.
	 *
	 * @param string $key Object key
	 * @param object $value Object
	 * @return object saved object
	 */
	public static function set(string $key, $value)
	{
		self::getInstance()->$key = $value;

		return $value;
	}

	/**
	 * Returns an object from an own storage.
	 *
	 * @param string $key Object key
	 * @return object
	 */
	public function __get(string $key)
	{
		return $this->storage[$key] ?? null;
	}

	/**
	 * Saves an object into an own storage.
	 *
	 * @param string $key Object key
	 * @param object $value Object
	 */
	public function __set(string $key, $value): void
	{
		$this->storage[$key] = $value;
	}

	/**
	 * Returns if there's an object with key $key in the storage.
	 *
	 * @param string $key Object key
	 * @return bool
	 */
	public function __isset(string $key): bool
	{
		return isset($this->storage[$key]);
	}

	/**
	 * Deletes an object with key $key from the storage.
	 *
	 * @param mixed $key Object key
	 */
	public function __unset($key): void
	{
		unset($this->storage[$key]);
	}

}
