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
 * Validates a value using a regular expression.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 */
class Regex extends \Jyxo\Input\Validator\AbstractValidator
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
	public function __construct($pattern)
	{
		$this->setPattern($pattern);
	}

	/**
	 * Sets the validation regular expression.
	 *
	 * @param string $pattern Regular expression
	 * @return \Jyxo\Input\Validator\Regex
	 * @throws \Jyxo\Input\Validator\Exception On empty regular expression
	 */
	public function setPattern($pattern)
	{
		if (empty($pattern)) {
			throw new \Jyxo\Input\Validator\Exception('Pattern could not be empty');
		}
		$this->pattern = (string) $pattern;

		return $this;
	}

	/**
	 * Returns the validation regular expression.
	 *
	 * @return string
	 */
	public function getPattern()
	{
		return $this->pattern;
	}

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	public function isValid($value)
	{
		if (!preg_match($this->getPattern(), (string) $value)) {
			return false;
		}

		return true;
	}

}
