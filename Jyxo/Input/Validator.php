<?php

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

/**
 * Class for easier one-line validation.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
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
	 * @return boolean
	 */
	public static function __callStatic($method, array $params)
	{
		$factory = \Jyxo\Spl\ObjectCache::get('\Jyxo\Input\Factory') ?: \Jyxo\Spl\ObjectCache::set('\Jyxo\Input\Factory', new \Jyxo\Input\Factory());
		$value = array_shift($params);
		$key = '\Jyxo\Input\Validator\\' . ucfirst($method) . ($params ? '/' . serialize($params) : '');
		$validator = \Jyxo\Spl\ObjectCache::get($key) ?: \Jyxo\Spl\ObjectCache::set($key, $factory->getValidatorByName($method, $params));
		return $validator->isValid($value);
	}
}
