<?php

/**
 * Testovací třída pro RPC.
 */
class TestMathWithCallStatic
{
	/**
	 * Volání přes magickou metodu.
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public static function __callStatic($method, $args)
	{
		return call_user_func_array(array(__CLASS__, 'difference'), $args);
	}

	/**
	 * Odečte dvě čísla.
	 *
	 * @param integer $a
	 * @param integer $b
	 * @return integer
	 */
	private static function difference($a, $b)
	{
		return $a - $b;
	}
}
