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
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Throwable;
use function sprintf;

/**
 * \Jyxo\Mail\Email\Address class test.
 *
 * @see \Jyxo\Mail\Email\Address
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class AddressTest extends TestCase
{

	/**
	 * Runs the test.
	 */
	public function test(): void
	{
		$email = 'jyxo@jyxo.com';
		$name = 'Jyxo';

		// Email and name given
		$address = new Address($email, $name);
		$this->assertEquals($email, $address->getEmail());
		$this->assertEquals($name, $address->getName());

		// Only email given
		$address = new Address($email);
		$this->assertEquals($email, $address->getEmail());
		$this->assertEquals('', $address->getName());

		// It is necessary to trim whitespace
		$address = new Address(' ' . $email, $name . ' ');
		$this->assertEquals($email, $address->getEmail());
		$this->assertEquals($name, $address->getName());

		// Invalid email
		try {
			$address = new Address('žlutý kůň@jyxo.com', $name);
			$this->fail(sprintf('Expected exception %s.', InvalidArgumentException::class));
		} catch (AssertionFailedError $e) {
			throw $e;
		} catch (Throwable $e) {
			// Correctly thrown exception
			$this->assertInstanceOf(InvalidArgumentException::class, $e);
		}
	}

}
