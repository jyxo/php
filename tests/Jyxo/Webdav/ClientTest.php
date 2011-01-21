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

namespace Jyxo\Webdav;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * Test for the \Jyxo\Webdav\Client class.
 *
 * @see \Jyxo\Webdav\Client
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Webdav client instance.
	 *
	 * @var \Jyxo\Webdav\Client
	 */
	private $client = null;

	/**
	 * Testing directory.
	 *
	 * @var string
	 */
	private $dir = 'test';

	/**
	 * Tested file.
	 *
	 * @var string
	 */
	private $file = 'test/test.txt';

	/**
	 * Prepares the testing environment.
	 */
	protected function setUp()
	{
		// Skips tests if there is no client configuration
		if (empty($GLOBALS['webdav'])) {
			$this->markTestSkipped('Webdav not set');
		}

		// Configures the client
		$this->client = new Client(array($GLOBALS['webdav']));
		$this->client->setOption('timeout', 5);
	}

	/**
	 * Cleans up the testing environment.
	 */
	protected function tearDown()
	{
		$this->client = null;
	}

	/**
	 * Tests a malfunctional server.
	 */
	public function testRequestError()
	{
		$this->setExpectedException('\Jyxo\Webdav\Exception');
		$client = new Client(array('127.0.0.1:5555'));
		$client->exists($this->file);
	}

	/**
	 * Tests a malfunctional server.
	 */
	public function testPoolError()
	{
		$this->setExpectedException('\Jyxo\Webdav\Exception');
		$client = new Client(array('127.0.0.1:5555'));
		$client->put($this->file, 'test');
	}

	/**
	 * Tests creating a directory.
	 */
	public function testMkdir()
	{
		$this->assertTrue($this->client->mkdir($this->dir));
		$this->assertTrue($this->client->mkdir($this->dir . '/aaa/bbb/ccc'));

		// Already exists
		$this->assertTrue($this->client->mkdir($this->dir));

		// Could not be created
		$this->assertFalse($this->client->mkdir($this->dir . '/xxx/yyy/zzz', false));
	}

	/**
	 * Tests uploading data.
	 *
	 * @depends testMkdir
	 */
	public function testPut()
	{
		$this->assertTrue($this->client->put($this->file, 'test'));
		$this->assertTrue($this->client->exists($this->file));

		// Auto-creating directories
		$this->assertTrue($this->client->put($this->dir . '/bbb/ccc/ddd/test.txt', 'test'));
		$this->assertTrue($this->client->exists($this->dir . '/bbb/ccc/ddd/test.txt'));
	}

	/**
	 * Tests uploading a file.
	 *
	 * @depends testMkdir
	 */
	public function testPutFile()
	{
		if (empty($GLOBALS['tmp'])) {
			$this->markTestSkipped('Temp dir not set');
		}

		// Create a temporary file
		$file = $GLOBALS['tmp'] . '/testfile.txt';
		file_put_contents($file, 'test');

		// Uploading
		$this->assertTrue($this->client->putFile($this->dir . '/testfile.txt', $file));
		$this->assertTrue($this->client->exists($this->dir . '/testfile.txt'));

		// Delete the temporary file
		@unlink($file);
	}

	/**
	 * Tests file existence.
	 *
	 * @depends testPut
	 */
	public function testExists()
	{
		$this->assertTrue($this->client->exists($this->file));
		$this->assertFalse($this->client->exists($this->dir . '/dummy.txt'));

		// \Directory must return false
		$this->assertFalse($this->client->exists($this->dir));
	}

	/**
	 * Tests file copying.
	 *
	 * @depends testPut
	 */
	public function testCopy()
	{
		// Lighttpd does not support this method.
		// $this->assertTrue($this->client->copy($this->file, $this->dir . '/testcopy.txt'));
		$this->assertFalse($this->client->copy($this->dir . '/dummy.txt', $this->dir . '/dummycopy.txt'));
	}

	/**
	 * Tests file renaming.
	 *
	 * @depends testPut
	 */
	public function testRename()
	{
		// Lighttpd does not support this method.
		// $this->assertTrue($this->client->rename($this->file, $this->dir . '/testrename.txt'));
		$this->assertFalse($this->client->rename($this->dir . '/dummy.txt', $this->dir . '/dummyrename.txt'));
	}

	/**
	 * Tests file contents retrieving.
	 *
	 * @depends testPut
	 */
	public function testGet()
	{
		$this->assertEquals('test', $this->client->get($this->file));
	}

	/**
	 * Tests file contents retrieving if the file does not exist.
	 */
	public function testGetIfFileNotExist()
	{
		$this->setExpectedException('\Jyxo\Webdav\FileNotExistException');
		$this->client->get($this->dir . '/dummy.txt');
	}

	/**
	 * Tests file contents retrieving if there was a directory name given.
	 *
	 * @depends testMkdir
	 */
	public function testGetIfDir()
	{
		$this->setExpectedException('\Jyxo\Webdav\FileNotExistException');
		$this->client->get($this->dir);
	}

	/**
	 * Tests retrieving file properties.
	 *
	 * @depends testPut
	 */
	public function testGetProperty()
	{
		// Returning one property value
		$this->assertGreaterThanOrEqual(new \DateTime(), new \DateTime($this->client->getProperty($this->file, 'getlastmodified')));

		// Returning all property values
		$this->assertArrayHasKey('getlastmodified', $this->client->getProperty($this->file));
	}

	/**
	 * Tests retrieving file properties if the file does not exist.
	 *
	 * @depends testMkdir
	 */
	public function testGetPropertyIfFileNotExist()
	{
		$this->setExpectedException('\Jyxo\Webdav\FileNotExistException');
		$this->client->getProperty($this->dir . '/dummy.txt');
	}

	/**
	 * Tests directory existence.
	 *
	 * @depends testMkdir
	 * @depends testPut
	 */
	public function testIsDir()
	{
		$this->assertTrue($this->client->isDir($this->dir));
		$this->assertFalse($this->client->isDir('dummy'));

		// The function must return false for files
		$this->assertFalse($this->client->isDir($this->file));
	}

	/**
	 * Tests deleting a file.
	 *
	 * @depends testIsDir
	 * @depends testGet
	 * @depends testGetProperty
	 * @depends testGetPropertyIfFileNotExist
	 */
	public function testUnlink()
	{
		$this->assertTrue($this->client->unlink($this->file));
		$this->assertFalse($this->client->unlink($this->dir . '/dummy.txt'));

		// The function must return false if a directory name is given
		$this->assertFalse($this->client->unlink($this->dir));
	}

	/**
	 * Tests deleting a directory.
	 *
	 * @depends testPutFile
	 * @depends testUnlink
	 * @depends testGetIfDir
	 */
	public function testRmdir()
	{
		// The function must return false for files
		$this->assertFalse($this->client->rmdir($this->dir . '/testfile.txt'));

		// Deleting a non-existent directory
		$this->assertFalse($this->client->rmdir('dummy'));

		// Final removing the testing directory
		$this->assertTrue($this->client->rmdir($this->dir));
	}
}
