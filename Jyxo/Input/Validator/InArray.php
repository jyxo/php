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
 * Checks if the given value is from an array of predefined values.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class InArray extends \Jyxo\Input\Validator\AbstractValidator
{
	/**
	 * Array of allowed values.
	 *
	 * @var array
	 */
	private $allowed = [];

	/**
	 * Constructor.
	 *
	 * @param array $allowed Array of allowed values
	 */
	public function __construct(array $allowed)
	{
		$this->allowed = $allowed;
	}

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	public function isValid($value)
	{
		return in_array($value, $this->allowed);
	}
}
