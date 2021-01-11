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

use InvalidArgumentException;
use function mb_strlen;

/**
 * Validates string length to be lower than the given length.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 */
class StringLengthLessThan extends AbstractValidator
{

	/**
	 * Maximal string length.
	 *
	 * @var int
	 */
	protected $max;

	/**
	 * Constructor.
	 *
	 * @param int $max Maximal string length (value length must be lower)
	 */
	public function __construct(int $max)
	{
		$this->setMax($max);
	}

	/**
	 * Sets the maximal string length.
	 *
	 * @param int $max Maximal string length
	 * @return StringLengthLessThan
	 */
	public function setMax(int $max): self
	{
		if ($max <= 0) {
			throw new InvalidArgumentException('Length of string must be greater than zero.');
		}

		$this->max = $max;

		return $this;
	}

	/**
	 * Returns the maximal string length.
	 *
	 * @return int
	 */
	public function getMax(): int
	{
		return $this->max;
	}

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	public function isValid($value): bool
	{
		return mb_strlen((string) $value, 'utf-8') < $this->getMax();
	}

}
