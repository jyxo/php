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

namespace Jyxo\Rpc;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use TestPhpInputStream;
use function file_get_contents;
use function ob_get_clean;
use function ob_start;
use function preg_replace;
use function unlink;
use function version_compare;
use const PHP_VERSION;

/**
 * Test for all \Jyxo\Rpc\Server child classes.
 *
 * @see \Jyxo\Rpc\Json\Server
 * @see \Jyxo\Rpc\Xml\Server
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 * @author Jaroslav Hanslík
 */
abstract class ServerTestCase extends TestCase
{

	/**
	 * RPC server.
	 *
	 * @var Server
	 */
	private $rpc = null;

	/**
	 * Returns server instance.
	 *
	 * @return Server
	 */
	abstract protected function getServerInstance(): Server;

	/**
	 * Returns test files extension.
	 *
	 * @return string
	 */
	abstract protected function getFileExtension(): string;

	/**
	 * Tests method call using the full name.
	 */
	public function testProcessMethodWithFullName(): void
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerMethod('TestMath', 'sum');

		$this->checkServerOutput('sum');
	}

	/**
	 * Tests method call using the short name.
	 */
	public function testProcessMethodWithShortName(): void
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerMethod('TestMath', 'sum', false);

		$this->checkServerOutput('sum-short');
	}

	/**
	 * Tests method call using a static method.
	 */
	public function testProcessStaticMethod(): void
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerMethod('TestMath', 'max');

		$this->checkServerOutput('max');
	}

	/**
	 * Tests method call using a __call magic function.
	 */
	public function testProcessMethodByCall(): void
	{
		require_once $this->getFilePath('TestMathWithCall.php');
		$this->rpc->registerMethod('TestMathWithCall', 'abs');

		$this->checkServerOutput('abs');
	}

	/**
	 * Tests method call using a __callStatic magic function.
	 */
	public function testProcessMethodByCallStatic(): void
	{
		// Skips this test if not running PHP 5.3+
		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
			$this->markTestSkipped('Incompatible PHP version');
		}

		require_once $this->getFilePath('TestMathWithCallStatic.php');
		$this->rpc->registerMethod('TestMathWithCallStatic', 'diff');

		$this->checkServerOutput('diff');
	}

	/**
	 * Tests calling a method registered using the whole class.
	 */
	public function testProcessMethodRegisteredByClass(): void
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerClass('TestMath', false);

		$this->checkServerOutput('sum');
		$this->checkServerOutput('max');
	}

	/**
	 * Tests calling a non-existent method.
	 */
	public function testProcessNonExistingMethod(): void
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerClass('TestMath');

		$this->checkServerOutput('min');
	}

	/**
	 * Tests bad request (parse error)
	 */
	public function testParseError(): void
	{
		$this->checkServerOutput('parse-error');
	}

	/**
	 * Tests calling a function registered as a method.
	 */
	public function testProcessFunction(): void
	{
		require_once $this->getFilePath('testPow2.php');
		$this->rpc->registerFunc('testPow2');

		$this->checkServerOutput('pow2');
	}

	/**
	 * Tests logging.
	 */
	public function testLog(): void
	{
		// Skips this test if no temporary directory is defined
		if (empty($GLOBALS['tmp'])) {
			$this->markTestSkipped('Temp dir not set');
		}

		$logFile = $GLOBALS['tmp'] . '/rpc.log';

		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerMethod('TestMath', 'sum');
		$this->rpc->enableLogging($logFile, [self::class, 'logCallback']);

		// Server output check
		$this->checkServerOutput('sum');

		// Log check - it is necessary to replace the dynamically generated date
		$log = preg_replace('~^\[\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}\]~', '[2009-11-15 19:09:24]', file_get_contents($logFile));
		$this->assertStringEqualsFile($this->getFilePath('expected.log'), $log);

		// Log cleanup
		unlink($logFile);
	}

	/**
	 * Tests clone-preventing.
	 */
	public function testClone(): void
	{
		$this->expectException(LogicException::class);
		$clone = clone $this->rpc;
	}

	/**
	 * Tests setting an empty log file.
	 */
	public function testEmptyLogFile(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->rpc->enableLogging('');
	}

	/**
	 * Tests registering a non-existent function.
	 */
	public function testRegisterNonExistingFunction(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->rpc->registerFunc('dummy');
	}

	/**
	 * Tests registering a non-existent method.
	 */
	public function testRegisterNonExistingMethod(): void
	{
		require_once $this->getFilePath('TestMath.php');
		$this->expectException(InvalidArgumentException::class);
		$this->rpc->registerMethod('TestMath', 'dummy');
	}

	/**
	 * Tests registering a non-public method.
	 */
	public function testRegisterNonPublicMethod(): void
	{
		require_once $this->getFilePath('TestMath.php');
		$this->expectException(InvalidArgumentException::class);
		$this->rpc->registerMethod('TestMath', 'diff');
	}

	/**
	 * Tests registering a method of a non-existent class.
	 */
	public function testRegisterMethodInNonExistingClass(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->rpc->registerMethod('Dummy', 'dummy');
	}

	/**
	 * Tests registering of a non-existent class.
	 */
	public function testRegisterNonExistingClass(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->rpc->registerClass('Dummy');
	}

	/**
	 * Callback function to alter log messages.
	 *
	 * @param string $method Log method
	 * @param array $params Log parameters
	 * @param mixed $result Function result
	 * @return array
	 */
	public static function logCallback(string $method, array $params, $result): array
	{
		return [$method, $params, 5];
	}

	/**
	 * Sets the testing environment.
	 */
	protected function setUp(): void
	{
		// Server
		$this->rpc = $this->getServerInstance();
	}

	/**
	 * Cleans up the environment after testing.
	 */
	protected function tearDown(): void
	{
		$this->rpc = null;
	}

	/**
	 * Checks server response.
	 *
	 * @param string $test
	 */
	private function checkServerOutput(string $test): void
	{
		// Prepares the server
		require_once $this->getFilePath('TestPhpInputStream.php');
		TestPhpInputStream::register();
		TestPhpInputStream::setContent(file_get_contents($this->getFilePath($test . '.' . $this->getFileExtension())));

		// We need to capture the output
		ob_start();
		// On purpose @ because of the "headers already sent" warning
		@$this->rpc->process();
		$output = ob_get_clean();

		$this->assertStringEqualsFile($this->getFilePath($test . '-expected.' . $this->getFileExtension()), $output);

		// Server cleanup
		TestPhpInputStream::unregister();
	}

	/**
	 * Returns file path.
	 *
	 * @param string $file Filename
	 * @return string
	 */
	private function getFilePath(string $file): string
	{
		return DIR_FILES . '/rpc/' . $file;
	}

}
