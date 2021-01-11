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

namespace Jyxo\Mail;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Throwable;
use function file_get_contents;
use function sprintf;

/**
 * \Jyxo\Mail\Encoding class test.
 *
 * @see \Jyxo\Mail\Encoding
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class EncodingTest extends TestCase
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
	private $encodings = [];

	/**
	 * Tests the constructor.
	 *
	 * @see \Jyxo\Mail\Encoding::__construct()
	 */
	public function testConstruct(): void
	{
		$this->expectException(LogicException::class);
		$encoding = new Encoding();
	}

	/**
	 * Tests the isCompatible() method.
	 *
	 * @see \Jyxo\Mail\Encoding::isCompatible()
	 */
	public function testIsCompatible(): void
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
	public function testEncode(): void
	{
		$data = file_get_contents($this->filePath . '/email.html');

		foreach ($this->encodings as $encoding) {
			$encoded = Encoding::encode($data, $encoding, 75, "\n");
			$this->assertStringEqualsFile($this->filePath . '/encoding-' . $encoding . '.txt', $encoded);
		}

		try {
			Encoding::encode('data', 'dummy-encoding', 75, "\n");
			$this->fail(sprintf('Expected exception %s.', InvalidArgumentException::class));
		} catch (AssertionFailedError $e) {
			throw $e;
		} catch (Throwable $e) {
			// Correctly thrown exception
			$this->assertInstanceOf(InvalidArgumentException::class, $e);
		}
	}

	/**
	 * Prepares the testing environment.
	 */
	protected function setUp(): void
	{
		$this->filePath = DIR_FILES . '/mail';

		$reflection = new ReflectionClass(Encoding::class);
		$this->encodings = $reflection->getConstants();
	}

}
