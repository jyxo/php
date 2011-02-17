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
 * Validates string length; must be between the given bounds.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class StringLengthBetween extends \Jyxo\Input\Validator\AbstractValidator
{
	/**
	 * Minimal string length.
	 *
	 * @var integer
	 */
	protected $min;

	/**
	 * Maximal string length.
	 *
	 * @var integer
	 */
	protected $max;

	/**
	 * Constructor.
	 *
	 * Sets both maximal and minimal string length.
	 *
	 * @param integer $min Minimal length (string length must be greater of equal)
	 * @param integer $max Maximal length (string length must be less or equal)
	 * @throws \InvalidArgumentException Arguments are invalid (less than zero or minimum is greater than maximum)
	 */
	public function __construct($min, $max)
	{
		$this->setMax($max);
		$this->setMin($min);
	}

	/**
	 * Sets the minimal string length.
	 *
	 * @param integer $min Minimal string length
	 * @return \Jyxo\Input\Validator\StringLengthBetween
	 * @throws \InvalidArgumentException If the minimal length is negative or greater than the maximal length
	 */
	public function setMin($min)
	{
		$min = (int) $min;
		if ($min < 0) {
			throw new \InvalidArgumentException('Length of string must be greater than zero.');
		}
		if ($min > $this->getMax()) {
			throw new \InvalidArgumentException('Min length must be lower or equal to max length.');
		}

		$this->min = $min;
		return $this;
	}

	/**
	 * Return the minimal string length.
	 *
	 * @return integer
	 */
	public function getMin()
	{
		return $this->min;
	}

	/**
	 * Sets the maximal string length.
	 *
	 * @param integer $max Maximal string length
	 * @return \Jyxo\Input\Validator\StringLengthBetween
	 * @throws \InvalidArgumentException If the maximal length is negative or lower than the minimal length
	 */
	public function setMax($max)
	{
		$max = (int) $max;
		if ($max <= 0) {
			throw new \InvalidArgumentException('Length of string must be greater than zero.');
		}
		if ($max < $this->getMin()) {
			throw new \InvalidArgumentException('Min length must be lower or equal to max length.');
		}

		$this->max = $max;

		return $this;
	}

	/**
	 * Returns the maximum string length.
	 *
	 * @return integer
	 */
	public function getMax()
	{
		return $this->max;
	}

	/**
	 * Validates a value.
	 *
	 * @param string $value Input value
	 * @return boolean
	 */
	public function isValid($value)
	{
		$length = mb_strlen((string) $value, 'utf-8');
		return ($length >= $this->getMin()) && ($length <= $this->getMax());
	}
}
