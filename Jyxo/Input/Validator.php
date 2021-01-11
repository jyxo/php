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
use Throwable;
use function array_shift;
use function serialize;
use function ucfirst;

/**
 * Class for easier one-line validation.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class Validator
{

	/**
	 * Static validation.
	 *
	 * @param string $method Validator name
	 * @param array $params Parameters; the first value gets validated, the rest will be used as constructor parameters
	 * @return bool
	 */
	public static function __callStatic(string $method, array $params): bool
	{
		try {
			$factory = ObjectCache::get(Factory::class) ?: ObjectCache::set(
				Factory::class,
				new Factory()
			);
			$value = array_shift($params);
			$key = 'Jyxo\Input\Validator\\' . ucfirst($method) . ($params ? '/' . serialize($params) : '');
			$validator = ObjectCache::get($key) ?: ObjectCache::set(
				$key,
				$factory->getValidatorByName($method, $params)
			);
		} catch (Throwable $e) {
			$validator = $factory->getValidatorByName($method, $params);
		}

		return $validator->isValid($value);
	}

}
