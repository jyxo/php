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

namespace Jyxo\Mail;

/**
 * \Jyxo\Mail\Encoding class test.
 *
 * @see \Jyxo\Mail\Encoding
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class EncodingTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Files path.
	 *
	 * @var string
	 */
	private $filePath;

	/**
	 * List of supported encodings.
	 *
	 * @var array
	 */
	private $encodings = array();

	/**
	 * Prepares the testing environment.
	 */
	protected function setUp()
	{
		$this->filePath = DIR_FILES . '/mail';

		$reflection = new \ReflectionClass('\Jyxo\Mail\Encoding');
		$this->encodings = $reflection->getConstants();
	}

	/**
	 * Tests the constructor.
	 *
	 * @see \Jyxo\Mail\Encoding::__construct()
	 */
	public function testConstruct()
	{
		$this->setExpectedException('\LogicException');
		$encoding = new Encoding();
	}

	/**
	 * Tests the isCompatible() method.
	 *
	 * @see \Jyxo\Mail\Encoding::isCompatible()
	 */
	public function testIsCompatible()
	{
		// All defined encodings are compatible
		foreach ($this->encodings as $encoding) {
			$this->assertTrue(Encoding::isCompatible($encoding));
		}

		// Incompatible encodings returns false
		$this->assertFalse(Encoding::isCompatible('dummy-encoding'));
	}

	/**
	 * Tests the encode() method.
	 *
	 * @see \Jyxo\Mail\Encoding::encode()
	 */
	public function testEncode()
	{
		$data = file_get_contents($this->filePath . '/email.html');
		foreach ($this->encodings as $encoding) {
			$encoded = Encoding::encode($data, $encoding, 75, "\n");
			$this->assertStringEqualsFile($this->filePath . '/encoding-' . $encoding . '.txt', $encoded);
		}

		try {
			Encoding::encode('data', 'dummy-encoding', 75, "\n");
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}
	}
}
