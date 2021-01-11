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

use function preg_match;

/**
 * Validates a value using a regular expression.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 */
class Regex extends AbstractValidator
{

	/**
	 * Regular expression.
	 *
	 * @var string
	 */
	protected $pattern;

	/**
	 * Constructor.
	 *
	 * @param string $pattern Regular expression
	 */
	public function __construct(string $pattern)
	{
		$this->setPattern($pattern);
	}

	/**
	 * Sets the validation regular expression.
	 *
	 * @param string $pattern Regular expression
	 * @return Regex
	 */
	public function setPattern(string $pattern): self
	{
		if (empty($pattern)) {
			throw new Exception('Pattern could not be empty');
		}

		$this->pattern = $pattern;

		return $this;
	}

	/**
	 * Returns the validation regular expression.
	 *
	 * @return string
	 */
	public function getPattern(): string
	{
		return $this->pattern;
	}

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	public function isValid($value): bool
	{
		return preg_match($this->getPattern(), (string) $value) === 1;
	}

}
