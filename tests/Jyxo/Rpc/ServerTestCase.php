<?php

/**
 * Jyxo Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/jyxo/php/blob/master/license.txt
 */

namespace Jyxo\Rpc;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * Test for all \Jyxo\Rpc\Server child classes.
 *
 * @see \Jyxo\Rpc\Json\Server
 * @see \Jyxo\Rpc\Xml\Server
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 * @author Jaroslav Hanslík
 */
abstract class ServerTestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * RPC server.
	 *
	 * @var \Jyxo\Rpc\Server
	 */
	private $rpc = null;

	/**
	 * Sets the testing environment.
	 */
	protected function setUp()
	{
		// Server
		$this->rpc = $this->getServerInstance();
	}

	/**
	 * Cleans up the environment after testing.
	 */
	protected function tearDown()
	{
		$this->rpc = null;
	}

	/**
	 * Tests method call using the full name.
	 */
	public function testProcessMethodWithFullName()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerMethod('TestMath', 'sum');

		$this->checkServerOutput('sum');
	}

	/**
	 * Tests method call using the short name.
	 */
	public function testProcessMethodWithShortName()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerMethod('TestMath', 'sum', false);

		$this->checkServerOutput('sum-short');
	}

	/**
	 * Tests method call using a static method.
	 */
	public function testProcessStaticMethod()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerMethod('TestMath', 'max');

		$this->checkServerOutput('max');
	}

	/**
	 * Tests method call using a __call magic function.
	 */
	public function testProcessMethodByCall()
	{
		require_once $this->getFilePath('TestMathWithCall.php');
		$this->rpc->registerMethod('TestMathWithCall', 'abs');

		$this->checkServerOutput('abs');
	}

	/**
	 * Tests method call using a __callStatic magic function.
	 */
	public function testProcessMethodByCallStatic()
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
	public function testProcessMethodRegisteredByClass()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerClass('TestMath', false);

		$this->checkServerOutput('sum');
		$this->checkServerOutput('max');
	}

	/**
	 * Tests calling a non-existent method.
	 */
	public function testProcessNonExistingMethod()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerClass('TestMath');

		$this->checkServerOutput('min');
	}

	/**
	 * Tests bad request (parse error)
	 */
	public function testParseError()
	{
		$this->checkServerOutput('parse-error');
	}


	/**
	 * Tests calling a function registered as a method.
	 */
	public function testProcessFunction()
	{
		require_once $this->getFilePath('testPow2.php');
		$this->rpc->registerFunc('testPow2');

		$this->checkServerOutput('pow2');
	}

	/**
	 * Tests logging.
	 */
	public function testLog()
	{
		// Skips this test if no temporary directory is defined
		if (empty($GLOBALS['tmp'])) {
			$this->markTestSkipped('Temp dir not set');
		}

		$logFile = $GLOBALS['tmp'] . '/rpc.log';

		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerMethod('TestMath', 'sum');
		$this->rpc->enableLogging($logFile, array(__CLASS__, 'logCallback'));

		// Server output check
		$this->checkServerOutput('sum');

		// Log check - it is necessary to replace the dynamically generated date
		$log = preg_replace('~^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]~', '[2009-11-15 19:09:24]', file_get_contents($logFile));
		$this->assertStringEqualsFile($this->getFilePath('expected.log'), $log);

		// Log cleanup
		unlink($logFile);
	}

	/**
	 * Tests clone-preventing.
	 */
	public function testClone()
	{
		$this->setExpectedException('LogicException');
		$clone = clone $this->rpc;
	}

	/**
	 * Tests setting an empty log file.
	 */
	public function testEmptyLogFile()
	{
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->enableLogging('');
	}

	/**
	 * Tests registering a not callable function.
	 */
	public function testInvalidLogCallback()
	{
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->enableLogging('file.log', 'dummy');
	}

	/**
	 * Tests registering a non-existent function.
	 */
	public function testRegisterNonExistingFunction()
	{
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->registerFunc('dummy');
	}

	/**
	 * Tests registering a non-existent method.
	 */
	public function testRegisterNonExistingMethod()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->registerMethod('TestMath', 'dummy');
	}

	/**
	 * Tests registering a non-public method.
	 */
	public function testRegisterNonPublicMethod()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->registerMethod('TestMath', 'diff');
	}

	/**
	 * Tests registering a method of a non-existent class.
	 */
	public function testRegisterMethodInNonExistingClass()
	{
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->registerMethod('Dummy', 'dummy');
	}

	/**
	 * Tests registering of a non-existent class.
	 */
	public function testRegisterNonExistingClass()
	{
		$this->setExpectedException('InvalidArgumentException');
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
	public static function logCallback($method, array $params, $result)
	{
		return array($method, $params, 5);
	}

	/**
	 * Checks server response.
	 *
	 * @param string $test
	 */
	private function checkServerOutput($test)
	{
		// Prepares the server
		require_once $this->getFilePath('TestPhpInputStream.php');
		\TestPhpInputStream::register();
		\TestPhpInputStream::setContent(file_get_contents($this->getFilePath($test . '.' . $this->getFileExtension())));

		// We need to capture the output
		ob_start();
		// On purpose @ because of the "headers already sent" warning
		@$this->rpc->process();
		$output = ob_get_clean();

		$this->assertStringEqualsFile($this->getFilePath($test . '-expected.' . $this->getFileExtension()), $output);

		// Server cleanup
		\TestPhpInputStream::unregister();
	}

	/**
	 * Returns file path.
	 *
	 * @param string $file Filename
	 * @return string
	 */
	private function getFilePath($file)
	{
		return DIR_FILES . '/rpc/' . $file;
	}

	/**
	 * Returns server instance.
	 *
	 * @return \Jyxo\Rpc\Server
	 */
	abstract protected function getServerInstance();

	/**
	 * Returns test files extension.
	 *
	 * @return string
	 */
	abstract protected function getFileExtension();
}
