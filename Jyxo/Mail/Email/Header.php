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

namespace Jyxo\Mail\Email;

use Jyxo\Spl\BaseObject;

/**
 * Email header.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Header extends BaseObject
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
	public function __construct(string $name, string $value)
	{
		$this->setName($name);
		$this->setValue($value);
	}

	/**
	 * Returns header name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Sets header name.
	 *
	 * @param string $name Name
	 * @return Header
	 */
	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Returns value.
	 *
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	/**
	 * Sets value.
	 *
	 * @param string $value Value
	 * @return Header
	 */
	public function setValue(string $value): self
	{
		$this->value = $value;

		return $this;
	}

}
