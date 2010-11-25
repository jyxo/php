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
 * Test pro potomky třídy \Jyxo\Rpc\Server.
 *
 * @see \Jyxo\Rpc\Json\Server
 * @see \Jyxo\Rpc\Xml\Server
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček <libs@jyxo.com>
 * @author Jaroslav Hanslík <libs@jyxo.com>
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
	 * Nastaví prostředí pro testy.
	 */
	protected function setUp()
	{
		// Server
		$this->rpc = $this->getServerInstance();
	}

	/**
	 * Vyčistí prostředí po testech.
	 */
	protected function tearDown()
	{
		$this->rpc = null;
	}

	/**
	 * Otestuje volání metody dlouhým jménem.
	 */
	public function testProcessMethodWithFullName()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerMethod('TestMath', 'sum');

		$this->checkServerOutput('sum');
	}

	/**
	 * Otestuje volání metody krátkým jménem.
	 */
	public function testProcessMethodWithShortName()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerMethod('TestMath', 'sum', false);

		$this->checkServerOutput('sum-short');
	}

	/**
	 * Otestuje volání statické metody.
	 */
	public function testProcessStaticMethod()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerMethod('TestMath', 'max');

		$this->checkServerOutput('max');
	}

	/**
	 * Otestuje volání přes __call.
	 */
	public function testProcessMethodByCall()
	{
		require_once $this->getFilePath('TestMathWithCall.php');
		$this->rpc->registerMethod('TestMathWithCall', 'abs');

		$this->checkServerOutput('abs');
	}

	/**
	 * Otestuje volání přes __callStatic.
	 */
	public function testProcessMethodByCallStatic()
	{
		// Přeskočí test, pokud není k dispozici PHP 5.3+
		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
			$this->markTestSkipped('Incompatible PHP version');
		}

		require_once $this->getFilePath('TestMathWithCallStatic.php');
		$this->rpc->registerMethod('TestMathWithCallStatic', 'diff');

		$this->checkServerOutput('diff');
	}

	/**
	 * Otestuje volání metody zaregistrované přes celou třídu.
	 */
	public function testProcessMethodRegisteredByClass()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerClass('TestMath', false);

		$this->checkServerOutput('sum');
		$this->checkServerOutput('max');
	}

	/**
	 * Otestuje volání neexistující metody.
	 */
	public function testProcessNonExistingMethod()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerClass('TestMath');

		$this->checkServerOutput('min');
	}

	/**
	 * Otestuje volání funkce.
	 */
	public function testProcessFunction()
	{
		require_once $this->getFilePath('testPow2.php');
		$this->rpc->registerFunc('testPow2');

		$this->checkServerOutput('pow2');
	}

	/**
	 * Otestuje logování.
	 */
	public function testLog()
	{
		// Přeskočí test, pokud není nastaven tmp adresář
		if (empty($GLOBALS['tmp'])) {
			$this->markTestSkipped('Temp dir not set');
		}

		$logFile = $GLOBALS['tmp'] . '/rpc.log';

		require_once $this->getFilePath('TestMath.php');
		$this->rpc->registerMethod('TestMath', 'sum');
		$this->rpc->enableLogging($logFile, array(__CLASS__, 'logCallback'));

		// Kontrola výstupu ze serveru
		$this->checkServerOutput('sum');

		// Kontrola logu - je třeba nahradit dynamicky generované datum
		$log = preg_replace('~^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]~', '[2009-11-15 19:09:24]', file_get_contents($logFile));
		$this->assertStringEqualsFile($this->getFilePath('expected.log'), $log);

		// Vyčištění logu
		unlink($logFile);
	}

	/**
	 * Nelze klonovat.
	 */
	public function testClone()
	{
		$this->setExpectedException('LogicException');
		$clone = clone $this->rpc;
	}

	/**
	 * Nelze nastavit prázdný soubor pro logování.
	 */
	public function testEmptyLogFile()
	{
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->enableLogging('');
	}

	/**
	 * Nelze nastavit funkci, kterou nelze zavolat.
	 */
	public function testInvalidLogCallback()
	{
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->enableLogging('file.log', 'dummy');
	}

	/**
	 * Nelze zaregistrovat neexistující funkci.
	 */
	public function testRegisterNonExistingFunction()
	{
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->registerFunc('dummy');
	}

	/**
	 * Nelze zaregistrovat neexistující metodu.
	 */
	public function testRegisterNonExistingMethod()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->registerMethod('TestMath', 'dummy');
	}

	/**
	 * Nelze zaregistrovat neveřejnou metodu.
	 */
	public function testRegisterNonPublicMethod()
	{
		require_once $this->getFilePath('TestMath.php');
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->registerMethod('TestMath', 'diff');
	}

	/**
	 * Nelze zaregistrovat metodu neexistující třídy.
	 */
	public function testRegisterMethodInNonExistingClass()
	{
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->registerMethod('Dummy', 'dummy');
	}

	/**
	 * Nelze zaregistrovat neexistující třídu.
	 */
	public function testRegisterNonExistingClass()
	{
		$this->setExpectedException('InvalidArgumentException');
		$this->rpc->registerClass('Dummy');
	}

	/**
	 * Funkce pro úpravu data před logováním.
	 *
	 * @param string $method
	 * @param array $params
	 * @param mixed $result
	 * @return array
	 */
	public static function logCallback($method, array $params, $result)
	{
		return array($method, $params, 5);
	}

	/**
	 * Zkontroluje výstup serveru.
	 *
	 * @param string $test
	 */
	private function checkServerOutput($test)
	{
		// Připraví server
		require_once $this->getFilePath('TestPhpInputStream.php');
		\TestPhpInputStream::register();
		\TestPhpInputStream::setContent(file_get_contents($this->getFilePath($test . '.' . $this->getFileExtension())));

		// Je třeba odchytit výstup
		ob_start();
		// Schválně @ kvůli chybě ohledně odeslaných hlaviček
		@$this->rpc->process();
		$output = ob_get_clean();

		$this->assertStringEqualsFile($this->getFilePath($test . '-expected.' . $this->getFileExtension()), $output);

		// Vyčistí server
		\TestPhpInputStream::unregister();
	}

	/**
	 * Vrátí cestu k souboru.
	 *
	 * @param string $file
	 * @return string
	 */
	private function getFilePath($file)
	{
		return DIR_FILES . '/rpc/' . $file;
	}

	/**
	 * Vrací instanci daného serveru
	 *
	 * @return \Jyxo\Rpc\Server
	 */
	abstract protected function getServerInstance();

	/**
	 * Vrací příponu testovaných souborů
	 *
	 * @return string
	 */
	abstract protected function getFileExtension();
}
