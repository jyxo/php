<?php

/**
 * Testing class for RPC.
 */
class TestMathWithCallStatic
{
	/**
	 * Calls using the magic method.
	 *
	 * @param string $method Method name
	 * @param array $args Method parameters
	 * @return mixed
	 */
	public static function __callStatic($method, $args)
	{
		return call_user_func_array(array(__CLASS__, 'difference'), $args);
	}

	/**
	 * Substracts two numbers.
	 *
	 * @param integer $a First number
	 * @param integer $b Second number
	 * @return integer
	 */
	private static function difference($a, $b)
	{
		return $a - $b;
	}
}
