<?php

/**
 * Testing object.
 */
class Foo extends \Jyxo\Spl\Object
{
	/**
	 * First variable.
	 *
	 * @var integer
	 */
	private $x;

	/**
	 * Second variable.
	 *
	 * @var boolean
	 */
	private $y;

	/**
	 * Returns the first variable value.
	 *
	 * @return integer
	 */
	public function getX()
	{
		return $this->x;
	}

	/**
	 * Sets the firstvariable value.
	 *
	 * @param integer $x New value
	 */
	public function setX($x)
	{
		$this->x = (int) $x;
	}

	/**
	 * Returns the second variable value.
	 *
	 * @return integer
	 */
	public function isY()
	{
		return $this->y;
	}

	/**
	 * Sets the second variable value.
	 *
	 * @param boolean $y New value
	 */
	public function setY($y)
	{
		$this->y = (bool) $y;
	}
}
