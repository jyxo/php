<?php

/**
 * Testovací třída pro RPC.
 */
class TestMath
{
	/**
	 * Sečte dvě čísla.
	 *
	 * @param integer $a
	 * @param integer $b
	 * @return integer
	 */
	public function sum($a, $b)
	{
		return $a + $b;
	}

	/**
	 * Vrátí maximum ze tří čísel.
	 *
	 * @param integer $a
	 * @param integer $b
	 * @param integer $c
	 * @return integer
	 */
	public static function max($a, $b, $c)
	{
		return max($a, $b, $c);
	}

	/**
	 * Odečte dvě čísla.
	 *
	 * @param integer $a
	 * @param integer $b
	 * @return integer
	 */
	private function diff($a, $b)
	{
		return $a - $b;
	}
}
