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

namespace Jyxo;

/**
 * Timer for debugging purposes. Allows measuring multiple events simultaneously.
 *
 * @category Jyxo
 * @package Jyxo\Timer
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Štěpán Svoboda
 * @author Matěj Humpál
 */
final class Timer
{
	/**
	 * Array of start times.
	 *
	 * @var array
	 */
	private static $starts = array();

	/**
	 * Starts measuring. Returns timer name.
	 *
	 * @param string $name Custom timer name
	 * @return string
	 */
	public static function start($name = '')
	{
		$start = microtime(true);
		if (empty($name)) {
			$name = md5($start . rand(0, 100));
		}
		self::$starts[$name] = $start;
		return $name;
	}

	/**
	 * Returns the time difference in seconds.
	 *
	 * @param string $name Timer name
	 * @return float
	 */
	public static function stop($name)
	{
		if (isset(self::$starts[$name])) {
			$delta = microtime(true) - self::$starts[$name];
			unset(self::$starts[$name]);
			return $delta;
		}
		return 0;
	}

	/**
	 * Returns the time form the last function call.
	 * In case of first function call, function returns 0.
	 *
	 * @return float
	 */
	public static function timer()
	{
		static $time = 0;
		$previousTime = $time;
		$time = microtime(true);

		return (0 === $previousTime) ? 0 : ($time - $previousTime);
	}
}
