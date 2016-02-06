<?php declare(strict_types = 1);

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
 * Validator for numbers; checks if its value is less than...
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 */
class LessThan extends \Jyxo\Input\Validator\AbstractValidator
{
	/**
	 * Desired maximum value
	 *
	 * @var integer
	 */
	protected $max = 0;

	/**
	 * Constructor.
	 *
	 * Sets maximum value.
	 *
	 * @param integer $max Maximum value (value must be less)
	 */
	public function __construct(int $max)
	{
		$this->setMax($max);
	}

	/**
	 * Sets the maximum value.
	 *
	 * @param integer $max New maximum value
	 * @return \Jyxo\Input\Validator\LessThan
	 */
	public function setMax(int $max): self
	{
		$this->max = $max;

		return $this;
	}

	/**
	 * Returns the maximum value.
	 *
	 * @return integer
	 */
	public function getMax(): int
	{
		return $this->max;
	}

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	public function isValid($value): bool
	{
		return (int) $value < $this->getMax();
	}
}
