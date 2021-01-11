<?php declare(strict_types = 1);

/**
 * Testing interface of Iterator
 */
class Foo3 implements Iterator
{

	/**
	 * Data to be iteraed over.
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Internal array pointer.
	 *
	 * @var iteger
	 */
	private $pointer = 0;

	/**
	 * Constructor.
	 *
	 * @param array $data Data
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Returns the current item value.
	 *
	 * @return mixed
	 */
	public function current()
	{
		return $this->data[$this->pointer] ?? null;
	}

	/**
	 * Advances the internal pointer.
	 */
	public function next(): void
	{
		$this->pointer++;
	}

	/**
	 * Sets the internal pointer to 0.
	 */
	public function rewind(): void
	{
		$this->pointer = 0;
	}

	/**
	 * Returns if the current pointer position is valid.
	 *
	 * @return bool
	 */
	public function valid(): bool
	{
		return isset($this->data[$this->pointer]);
	}

	/**
	 * Returns the current pointer value.
	 *
	 * @return int
	 */
	public function key(): int
	{
		return $this->pointer;
	}

}
