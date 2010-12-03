<?php

/**
 * Testing class for RPC.
 */
class TestMath
{
	/**
	 * Adds two numbers.
	 *
	 * @param integer $a First number
	 * @param integer $b Second number
	 * @return integer
	 */
	public function sum($a, $b)
	{
		return $a + $b;
	}

	/**
	 * Returns highest of three numbers.
	 *
	 * @param integer $a First number
	 * @param integer $b Second number
	 * @param integer $c Third number
	 * @return integer
	 */
	public static function max($a, $b, $c)
	{
		return max($a, $b, $c);
	}

	/**
	 * Substracts two numbers.
	 *
	 * @param integer $a First number
	 * @param integer $b Second number
	 * @return integer
	 */
	private function diff($a, $b)
	{
		return $a - $b;
	}
}
