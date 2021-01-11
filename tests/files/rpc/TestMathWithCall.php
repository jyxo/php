<?php declare(strict_types = 1);

/**
 * Testing class for RPC.
 */
class TestMathWithCall
{

	/**
	 * Calculates absolute value.
	 *
	 * @param int $a Input number
	 * @return int
	 */
	private function absolute(int $a): int
	{
		return abs($a);
	}

	/**
	 * Calls using the magic method.
	 *
	 * @param string $method Method name
	 * @param array $args Method parameters
	 * @return mixed
	 */
	public function __call(string $method, array $args)
	{
		return call_user_func_array([$this, 'absolute'], $args);
	}

}
