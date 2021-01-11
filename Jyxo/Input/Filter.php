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

namespace Jyxo\Input;

use Jyxo\Spl\ObjectCache;
use function array_shift;
use function serialize;
use function ucfirst;

/**
 * Class for easier one-line filtering.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class Filter
{

	/**
	 * Static filtering.
	 *
	 * @param string $method Filter name
	 * @param array $params Parameters; the first value gets filtered, the rest will be used as constructor parameters
	 * @return mixed
	 */
	public static function __callStatic(string $method, array $params)
	{
		$factory = ObjectCache::get(Factory::class) ?: ObjectCache::set(
			Factory::class,
			new Factory()
		);
		$value = array_shift($params);
		$key = 'Jyxo\Input\Filter\\' . ucfirst($method) . ($params ? '/' . serialize($params) : '');

		/** @var FilterInterface $filter */
		$filter = ObjectCache::get($key) ?: ObjectCache::set($key, $factory->getFilterByName($method, $params));

		return $filter->filter($value);
	}

}
