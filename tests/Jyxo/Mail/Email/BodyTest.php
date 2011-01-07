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

namespace Jyxo\Mail\Email;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * \Jyxo\Mail\Email\Body class test.
 *
 * @see \Jyxo\Mail\Email\Body
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class BodyTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Runs the test.
	 */
	public function test()
	{
		$html = file_get_contents(DIR_FILES . '/mail/email.html');
		$text = \Jyxo\Html::toText($html);

		// HTML and plaintext given
		$body = new Body($html, $text);
		$this->assertEquals($html, $body->getMain());
		$this->assertEquals($text, $body->getAlternative());
		$this->assertTrue($body->isHtml());

		// Only HTML
		$body = new Body($html);
		$this->assertEquals($html, $body->getMain());
		$this->assertTrue($body->isHtml());

		// Only plaintext
		$body = new Body($text);
		$this->assertEquals($text, $body->getMain());
		$this->assertFalse($body->isHtml());
	}
}
