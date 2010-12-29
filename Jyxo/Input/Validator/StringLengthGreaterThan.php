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
 * Validates string length to be greater than the given length.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 */
class StringLengthGreaterThan extends \Jyxo\Input\Validator\AbstractValidator
{
	/**
	 * Minimal string length.
	 *
	 * @var integer
	 */
	protected $min;

	/**
	 * Constructor.
	 *
	 * @param integer $min Minimal string length (value length must be greater)
	 */
	public function __construct($min)
	{
		$this->setMin($min);
	}

	/**
	 * Sets the minimal string length.
	 *
	 * @param integer $min Minimal string length
	 * @return \Jyxo\Input\Validator\StringLengthGreaterThan
	 * @throws \InvalidArgumentException If the minimal length is negative
	 */
	public function setMin($min)
	{
		$min = (int) $min;

		if ($min < 0) {
			throw new \InvalidArgumentException('Length of string must be greater than zero.');
		}

		$this->min = $min;

		return $this;
	}

	/**
	 * Returns the minimal string length.
	 *
	 * @return integer
	 */
	public function getMin()
	{
		return $this->min;
	}

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	public function isValid($value)
	{
		return mb_strlen((string) $value) > $this->getMin();
	}

}
