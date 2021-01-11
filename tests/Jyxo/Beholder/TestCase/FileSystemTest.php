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

namespace Jyxo\Beholder\TestCase;

use Jyxo\Beholder\Result;
use PHPUnit\Framework\TestCase;
use TestFileSystemStream;
use function sprintf;

/**
 * Test for the \Jyxo\Beholder\TestCase\FileSystem class.
 *
 * @see \Jyxo\Beholder\TestCase\FileSystem
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class FileSystemTest extends TestCase
{

	/**
	 * Protocol name.
	 *
	 * @var string
	 */
	private $protocol = 'test';

	/**
	 * Tested directory.
	 *
	 * @var string
	 */
	private $dir = 'test://';

	/**
	 * Tests write failure.
	 */
	public function testWriteFailure(): void
	{
		TestFileSystemStream::setError(TestFileSystemStream::ERROR_WRITE);

		$test = new FileSystem('FileSystem', $this->dir);
		// @ on purpose
		$result = @$test->run();
		$this->assertEquals(Result::FAILURE, $result->getStatus());
		$this->assertEquals(sprintf('Write error %s', $this->dir), $result->getDescription());
	}

	/**
	 * Tests read failure.
	 */
	public function testReadFailure(): void
	{
		TestFileSystemStream::setError(TestFileSystemStream::ERROR_READ);

		$test = new FileSystem('FileSystem', $this->dir);
		$result = $test->run();
		$this->assertEquals(Result::FAILURE, $result->getStatus());
		$this->assertEquals(sprintf('Read error %s', $this->dir), $result->getDescription());
	}

	/**
	 * Tests delete failure.
	 */
	public function testDeleteFailure(): void
	{
		TestFileSystemStream::setError(TestFileSystemStream::ERROR_DELETE);

		$test = new FileSystem('FileSystem', $this->dir);
		$result = $test->run();
		$this->assertEquals(Result::FAILURE, $result->getStatus());
		$this->assertEquals(sprintf('Delete error %s', $this->dir), $result->getDescription());
	}

	/**
	 * Tests all functions.
	 */
	public function testAllOk(): void
	{
		TestFileSystemStream::setError(TestFileSystemStream::ERROR_NONE);

		$test = new FileSystem('FileSystem', $this->dir);
		$result = $test->run();
		$this->assertEquals(Result::SUCCESS, $result->getStatus());
		$this->assertEquals($this->dir, $result->getDescription());
	}

	/**
	 * Prepares the testing environment..
	 */
	protected function setUp(): void
	{
		require_once DIR_FILES . '/beholder/TestFileSystemStream.php';
		TestFileSystemStream::register($this->protocol);
	}

	/**
	 * Cleans up the testing environment.
	 */
	protected function tearDown(): void
	{
		TestFileSystemStream::unregister($this->protocol);
	}

}
