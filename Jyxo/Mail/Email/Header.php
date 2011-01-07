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

namespace Jyxo\Mail\Email;

/**
 * Email header.
 *
 * @category Jyxo
 * @package Jyxo\Mail
 * @subpackage Email
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Header extends \Jyxo\Spl\Object
{
	/**
	 * Header name.
	 *
	 * @var string
	 */
	private $name = '';

	/**
	 * Value.
	 *
	 * @var string
	 */
	private $value = '';

	/**
	 * Creates a header.
	 *
	 * @param string $name Header name
	 * @param string $value Value
	 */
	public function __construct($name, $value)
	{
		$this->setName($name);
		$this->setValue($value);
	}

	/**
	 * Returns header name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Sets header name.
	 *
	 * @param string $name Name
	 * @return \Jyxo\Mail\Email\Header
	 */
	public function setName($name)
	{
		$this->name = (string) $name;

		return $this;
	}

	/**
	 * Returns value.
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Sets value.
	 *
	 * @param string $value Value
	 * @return \Jyxo\Mail\Email\Header
	 */
	public function setValue($value)
	{
		$this->value = (string) $value;

		return $this;
	}
}
