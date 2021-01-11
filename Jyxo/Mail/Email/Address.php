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

use InvalidArgumentException;
use Jyxo\Input\Validator\IsEmail;
use Jyxo\Spl\BaseObject;
use function sprintf;
use function trim;

/**
 * Email address.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Address extends BaseObject
{

	/**
	 * Email address.
	 *
	 * @var string
	 */
	private $email = '';

	/**
	 * Name.
	 *
	 * @var string
	 */
	private $name = '';

	/**
	 * Creates an address.
	 *
	 * @param string $email Email
	 * @param string $name Name
	 */
	public function __construct(string $email, string $name = '')
	{
		$this->setEmail($email);
		$this->setName($name);
	}

	/**
	 * Returns email address.
	 *
	 * @return string
	 */
	public function getEmail(): string
	{
		return $this->email;
	}

	/**
	 * Sets email address.
	 *
	 * @param string $email Email address
	 * @return Address
	 */
	public function setEmail(string $email): self
	{
		$email = trim($email);

		// Validity check
		if (!IsEmail::validate($email)) {
			throw new InvalidArgumentException(sprintf('Invalid email address %s.', $email));
		}

		$this->email = $email;

		return $this;
	}

	/**
	 * Returns name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Sets name.
	 *
	 * @param string $name Name
	 * @return Address
	 */
	public function setName(string $name): self
	{
		$this->name = trim($name);

		return $this;
	}

}
