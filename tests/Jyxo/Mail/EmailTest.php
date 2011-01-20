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

require_once __DIR__ . '/../../bootstrap.php';

/**
 * \Jyxo\Mail\Email class test.
 *
 * @see \Jyxo\Mail\Email\Body
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class EmailTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Runs the test.
	 */
	public function test()
	{
		$filePath = DIR_FILES . '/mail';

		$subject = 'Novinky září 2009 ... a kreslící soutěž';
		$from = new Email\Address('blog-noreply@blog.cz', 'Blog.cz');
		$to = array(
			new Email\Address('test1@blog.cz', 'Test Test1'),
			new Email\Address('test2@blog.cz')
		);
		$cc = array(
			new Email\Address('test3@blog.cz', 'Test Test3'),
			new Email\Address('test4@blog.cz')
		);
		$bcc = array(
			new Email\Address('test5@blog.cz', 'Test Test5')
		);

		$headers = array(
			new Email\Header('Organization', 'Blog.cz')
		);

		$inReplyTo = '161024ac03484c10203285be576446f2@blog.cz';
		$references = array('30d6c4933818e36fa46509ad24a91ea4@blog.cz', '8b30935de59b6c89e4fc1204d279a2af@blog.cz');

		$html = file_get_contents($filePath . '/email.html');
		$body = new Email\Body($html, \Jyxo\Html::toText($html));

		$attachments = array(
			new Email\Attachment\File($filePath . '/logo.gif', 'logo.gif', 'image/gif'),
			new Email\Attachment\String(file_get_contents($filePath . '/star.gif'), 'star.gif', 'image/gif')
		);
		$inlineAttachments = array(
			new Email\Attachment\InlineFile($filePath . '/logo.gif', 'logo.gif', 'logo.gif', 'image/gif'),
			new Email\Attachment\InlineString(file_get_contents($filePath . '/star.gif'), 'star.gif', 'star.gif', 'image/gif')
		);

		// Basic settings
		$email = new Email();
		$email->setSubject($subject)
			->setFrom($from)
			->addReplyTo($from)
			->setInReplyTo($inReplyTo, $references)
			->setConfirmReadingTo($from);
		$this->assertEquals($subject, $email->getSubject());
		$this->assertSame($from, $email->getFrom());
		$this->assertSame(array($from), $email->getReplyTo());
		$this->assertSame($from, $email->getConfirmReadingTo());
		$this->assertEquals($inReplyTo, $email->getInReplyTo());
		$this->assertSame($references, $email->getReferences());

		// Recipients
		foreach ($to as $address) {
			$email->addTo($address);
		}
		foreach ($cc as $address) {
			$email->addCc($address);
		}
		foreach ($bcc as $address) {
			$email->addBcc($address);
		}
		$this->assertSame($to, $email->getTo());
		$this->assertSame($cc, $email->getCc());
		$this->assertSame($bcc, $email->getBcc());

		// Priority
		$reflection = new \ReflectionClass('\Jyxo\Mail\Email');
		foreach ($reflection->getConstants() as $name => $value) {
			if (0 === strpos($name, 'PRIORITY_')) {
				$email->setPriority($value);
				$this->assertEquals($value, $email->getPriority());
			}
		}
		try {
			$email->setPriority('dummy-priority');
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}

		// Headers
		foreach ($headers as $header) {
			$email->addHeader($header);
		}
		$this->assertSame($headers, $email->getHeaders());

		// Body
		$email->setBody($body);
		$this->assertSame($body, $email->getBody());

		// Attachments
		foreach ($attachments as $attachment) {
			$email->addAttachment($attachment);
		}
		$this->assertSame($attachments, $email->getAttachments());
		$this->assertFalse($email->hasInlineAttachments());
		foreach ($inlineAttachments as $attachment) {
			$email->addAttachment($attachment);
		}
		$this->assertSame(array_merge($attachments, $inlineAttachments), $email->getAttachments());
		$this->assertTrue($email->hasInlineAttachments());
	}
}
