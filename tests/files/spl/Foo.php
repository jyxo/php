<?php

/**
 * Testovací objekt.
 */
class Foo extends \Jyxo\Spl\Object
{
	/**
	 * Testovací proměnná.
	 *
	 * @var integer
	 */
	private $x;

	/**
	 * Druhá testovací proměnná.
	 *
	 * @var boolean
	 */
	private $y;

	/**
	 * Vrátí proměnnou.
	 *
	 * @return integer
	 */
	public function getX()
	{
		return $this->x;
	}

	/**
	 * Nastaví proměnnou.
	 *
	 * @param int $x
	 */
	public function setX($x)
	{
		$this->x = (int) $x;
	}

	/**
	 * Vrátí druhou proměnnou.
	 *
	 * @return integer
	 */
	public function isY()
	{
		return $this->y;
	}

	/**
	 * Nastaví druhou proměnnou.
	 *
	 * @param bool $y
	 */
	public function setY($y)
	{
		$this->y = (bool) $y;
	}
}
