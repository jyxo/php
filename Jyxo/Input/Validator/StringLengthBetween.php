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
 * Validates string length; must be between the given bounds.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class StringLengthBetween extends AbstractValidator
{

	/**
	 * Minimal string length.
	 *
	 * @var int
	 */
	protected $min;

	/**
	 * Maximal string length.
	 *
	 * @var int
	 */
	protected $max;

	/**
	 * Constructor.
	 *
	 * Sets both maximal and minimal string length.
	 *
	 * @param int $min Minimal length (string length must be greater of equal)
	 * @param int $max Maximal length (string length must be less or equal)
	 */
	public function __construct(int $min, int $max)
	{
		$this->setMax($max);
		$this->setMin($min);
	}

	/**
	 * Sets the minimal string length.
	 *
	 * @param int $min Minimal string length
	 * @return StringLengthBetween
	 */
	public function setMin(int $min): self
	{
		if ($min < 0) {
			throw new InvalidArgumentException('Length of string must be greater than zero.');
		}

		if ($this->max !== null && $min > $this->max) {
			throw new InvalidArgumentException('Min length must be lower or equal to max length.');
		}

		$this->min = $min;

		return $this;
	}

	/**
	 * Return the minimal string length.
	 *
	 * @return int
	 */
	public function getMin(): int
	{
		return $this->min;
	}

	/**
	 * Sets the maximal string length.
	 *
	 * @param int $max Maximal string length
	 * @return StringLengthBetween
	 */
	public function setMax(int $max): self
	{
		if ($max <= 0) {
			throw new InvalidArgumentException('Length of string must be greater than zero.');
		}

		if ($this->min !== null && $max < $this->min) {
			throw new InvalidArgumentException('Min length must be lower or equal to max length.');
		}

		$this->max = $max;

		return $this;
	}

	/**
	 * Returns the maximum string length.
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
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 * @param string $value Input value
	 * @return bool
	 */
	public function isValid($value): bool
	{
		$length = mb_strlen((string) $value, 'utf-8');

		return ($length >= $this->getMin()) && ($length <= $this->getMax());
	}

}
