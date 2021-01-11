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
 * Validates string length to be greater than the given length.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 */
class StringLengthGreaterThan extends AbstractValidator
{

	/**
	 * Minimal string length.
	 *
	 * @var int
	 */
	protected $min;

	/**
	 * Constructor.
	 *
	 * @param int $min Minimal string length (value length must be greater)
	 */
	public function __construct(int $min)
	{
		$this->setMin($min);
	}

	/**
	 * Sets the minimal string length.
	 *
	 * @param int $min Minimal string length
	 * @return StringLengthGreaterThan
	 */
	public function setMin(int $min): self
	{
		if ($min < 0) {
			throw new InvalidArgumentException('Length of string must be greater than zero.');
		}

		$this->min = $min;

		return $this;
	}

	/**
	 * Returns the minimal string length.
	 *
	 * @return int
	 */
	public function getMin(): int
	{
		return $this->min;
	}

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	public function isValid($value): bool
	{
		return mb_strlen((string) $value, 'utf-8') > $this->getMin();
	}

}
