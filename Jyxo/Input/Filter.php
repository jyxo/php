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

namespace Jyxo\Input;

/**
 * Class for easier one-line filtering.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Filter
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
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
	public static function __callStatic($method, array $params)
	{
		$factory = \Jyxo\Spl\ObjectCache::get('\Jyxo\Input\Factory') ?: \Jyxo\Spl\ObjectCache::set('\Jyxo\Input\Factory', new \Jyxo\Input\Factory());
		$value = array_shift($params);
		$key = '\Jyxo\Input\Filter\\' . ucfirst($method) . ($params ? '/' . serialize($params) : '');
		$filter = \Jyxo\Spl\ObjectCache::get($key) ?: \Jyxo\Spl\ObjectCache::set($key, $factory->getFilterByName($method, $params));
		/* @var $filter \Jyxo\Input\FilterInterface */
		return $filter->filter($value);
	}
}
