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

namespace Jyxo\Mail\Email\Attachment;

use Jyxo\Mail\Email\Attachment;
use PHPUnit\Framework\TestCase;
use function file_get_contents;

/**
 * \Jyxo\Mail\Email\Attachment\FileAttachment class test.
 *
 * @see \Jyxo\Mail\Email\Attachment\File
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class FileAttachmentTest extends TestCase
{

	/**
	 * Runs the test.
	 */
	public function test(): void
	{
		$path = DIR_FILES . '/mail/logo.gif';
		$name = 'logo.gif';
		$mimeType = 'image/gif';

		$attachment = new FileAttachment($path, $name, $mimeType);
		$this->assertEquals(file_get_contents($path), $attachment->getContent());
		$this->assertEquals($name, $attachment->getName());
		$this->assertEquals($mimeType, $attachment->getMimeType());
		$this->assertEquals(Attachment::DISPOSITION_ATTACHMENT, $attachment->getDisposition());
		$this->assertFalse($attachment->isInline());
		$this->assertEquals('', $attachment->getCid());
		$this->assertEquals('', $attachment->getEncoding());
	}

}
