<?php

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
	private $data = array();

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
		return isset($this->data[$this->pointer]) ? $this->data[$this->pointer] : null;
	}

	/**
	 * Advances the internal pointer.
	 */
	public function next()
	{
		$this->pointer++;
	}

	/**
	 * Sets the internal pointer to 0.
	 */
	public function rewind()
	{
		$this->pointer = 0;
	}

	/**
	 * Returns if the current pointer position is valid.
	 *
	 * @return boolean
	 */
	public function valid()
	{
		return isset($this->data[$this->pointer]);
	}

	/**
	 * Returns the current pointer value.
	 *
	 * @return integer
	 */
	public function key()
	{
		return $this->pointer;
	}

}
