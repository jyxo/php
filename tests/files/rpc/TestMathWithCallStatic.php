<?php declare(strict_types = 1);

/**
 * Testing class for RPC.
 */
class TestMathWithCallStatic
{

	/**
	 * Substracts two numbers.
	 *
	 * @param int $a First number
	 * @param int $b Second number
	 * @return int
	 */
	private static function difference(int $a, int $b): int
	{
		return $a - $b;
	}

	/**
	 * Calls using the magic method.
	 *
	 * @param string $method Method name
	 * @param array $args Method parameters
	 * @return mixed
	 */
	public static function __callStatic(string $method, array $args)
	{
		return call_user_func_array([self::class, 'difference'], $args);
	}

}
