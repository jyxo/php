<?php

/**
 * Testing interface of IteratorAggregate.
 */
class Foo2 implements IteratorAggregate
{

	/**
	 * Data to be iteraed over.
	 *
	 * @var array
	 */
	private $data = array();

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
	 * Returns an iterator instance.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->data);
	}

}
