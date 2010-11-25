<?php

/**
 * Testovací třída pro RPC.
 */
class TestMathWithCall
{
	/**
	 * Volání přes magickou metodu.
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		return call_user_func_array(array($this, 'absolute'), $args);
	}

	/**
	 * Absolutní hodnota čísla
	 *
	 * @param integer $a
	 * @return integer
	 */
	private function absolute($a)
	{
		return abs($a);
	}
}
