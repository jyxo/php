<?php

/**
 * Jyxo PHP Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/jyxo/php/blob/master/license.txt
 */

namespace Jyxo\Input\Validator;

/**
 * Validates a value.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Equals extends \Jyxo\Input\Validator\AbstractValidator
{

	/**
	 * Expected value.
	 *
	 * @var mixed
	 */
	protected $expected;


	/**
	 * Constructor.
	 *
	 * @param mixed $expected Expected value
	 */
	public function __construct($expected)
	{
		$this->setExpected($expected);
	}

	/**
	 * Sets the expected value.
	 *
	 * @param mixed $expected Expected value
	 * @return \Jyxo\Input\Validator\Equals
	 */
	public function setExpected($expected)
	{
		$this->expected = $expected;

		return $this;
	}

	/**
	 * Returns the expected value.
	 *
	 * @return mixed
	 */
	public function getExpected()
	{
		return $this->expected;
	}

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	public function isValid($value)
	{
		return $value == $this->expected;
	}

}
