<?php declare(strict_types = 1);

/**
 * Testing class for RPC.
 */
class TestMath
{

	/**
	 * Adds two numbers.
	 *
	 * @param int $a First number
	 * @param int $b Second number
	 * @return int
	 */
	public function sum(int $a, int $b): int
	{
		return $a + $b;
	}

	/**
	 * Returns highest of three numbers.
	 *
	 * @param int $a First number
	 * @param int $b Second number
	 * @param int $c Third number
	 * @return int
	 */
	public static function max(int $a, int $b, int $c): int
	{
		return max($a, $b, $c);
	}

	/**
	 * Substracts two numbers.
	 *
	 * @param int $a First number
	 * @param int $b Second number
	 * @return int
	 */
	private function diff(int $a, int $b): int
	{
		return $a - $b;
	}

}
