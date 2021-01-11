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

use function in_array;

/**
 * Checks if the given value is from an array of predefined values.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class InArray extends AbstractValidator
{

	/**
	 * Array of allowed values.
	 *
	 * @var mixed[]
	 */
	private $allowed = [];

	/**
	 * Constructor.
	 *
	 * @param mixed[] $allowed Array of allowed values
	 */
	public function __construct(array $allowed)
	{
		$this->allowed = $allowed;
	}

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	public function isValid($value): bool
	{
		return in_array($value, $this->allowed, true);
	}

}
