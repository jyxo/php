<?php declare(strict_types = 1);

use Jyxo\Spl\BaseObject;

/**
 * Testing object.
 */
class Foo extends BaseObject
{

	/**
	 * First variable.
	 *
	 * @var int
	 */
	private $x;

	/**
	 * Second variable.
	 *
	 * @var bool
	 */
	private $y;

	/**
	 * Returns the first variable value.
	 *
	 * @return int
	 */
	public function getX(): int
	{
		return $this->x;
	}

	/**
	 * Sets the firstvariable value.
	 *
	 * @param int $x New value
	 */
	public function setX(int $x): void
	{
		$this->x = $x;
	}

	/**
	 * Returns the second variable value.
	 *
	 * @return bool
	 */
	public function isY(): bool
	{
		return $this->y;
	}

	/**
	 * Sets the second variable value.
	 *
	 * @param bool $y New value
	 */
	public function setY(bool $y): void
	{
		$this->y = $y;
	}

}
