<?php

/**
 * Testing class for RPC.
 */
class TestMathWithCall
{
	/**
	 * Calls using the magic method.
	 *
	 * @param string $method Method name
	 * @param array $args Method parameters
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		return call_user_func_array(array($this, 'absolute'), $args);
	}

	/**
	 * Calculates absolute value.
	 *
	 * @param integer $a Input number
	 * @return integer
	 */
	private function absolute($a)
	{
		return abs($a);
	}
}
