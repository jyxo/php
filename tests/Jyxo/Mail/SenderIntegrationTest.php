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

/**
 * \Jyxo\Mail\Sender class test.
 *
 * @see \Jyxo\Mail\Sender
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 * @group integration
 */
class SenderIntegrationTest extends \PHPUnit_Framework_TestCase
{
	use EmailTestHelpers;

	/**
	 * FileAttachment path.
	 *
	 * @var string
	 */
	private $filePath;

	/**
	 * Testing mail contents.
	 *
	 * @var string
	 */
	private $content;

	/**
	 * Prepares the testing environment.
	 */
	protected function setUp()
	{
		$this->filePath = DIR_FILES . '/mail';
		$this->content = file_get_contents($this->filePath . '/email.html');
	}

	/**
	 * Tests sending using the mail() function.
	 */
	public function testSendMail()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$this->markTestSkipped('Skipped on Windows');
		}

		$sender = new Sender();
		$sender->setEmail($this->getEmail());
		$result = $sender->send(Sender::MODE_MAIL);
		$this->assertResult('sender-type-simple-text.eml', $result);
	}

	/**
	 * Tests sending using a SMTP server.
	 */
	public function testSendSmtp()
	{
		// Skips the test if no smtp connection is set
		if (empty($GLOBALS['smtp'])) {
			$this->markTestSkipped('Smtp host not set');
		}

		$sender = new Sender();
		$sender->setEmail($this->getEmail())
			->setSmtp($GLOBALS['smtp']);
		$result = $sender->send(Sender::MODE_SMTP);
		$this->assertResult('sender-type-simple-text.eml', $result);
	}

}
