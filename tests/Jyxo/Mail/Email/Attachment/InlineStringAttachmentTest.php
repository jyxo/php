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

use InvalidArgumentException;
use Jyxo\Mail\Email\Attachment;
use Jyxo\Mail\Encoding;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Throwable;
use function file_get_contents;
use function sprintf;

/**
 * \Jyxo\Mail\Email\Attachment\InlineStringAttachment class test.
 *
 * @see \Jyxo\Mail\Email\Attachment\InlineString
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class InlineStringAttachmentTest extends TestCase
{

	/**
	 * Runs the test.
	 */
	public function test(): void
	{
		$content = file_get_contents(DIR_FILES . '/mail/logo.gif');
		$name = 'logo.gif';
		$cid = 'logo.gif';
		$mimeType = 'image/gif';

		$attachment = new InlineStringAttachment($content, $name, $cid, $mimeType);
		$this->assertEquals($content, $attachment->getContent());
		$this->assertEquals($name, $attachment->getName());
		$this->assertEquals($mimeType, $attachment->getMimeType());
		$this->assertEquals(Attachment::DISPOSITION_INLINE, $attachment->getDisposition());
		$this->assertTrue($attachment->isInline());
		$this->assertEquals($cid, $attachment->getCid());
		$this->assertEquals('', $attachment->getEncoding());

		// It is possible to set an encoding
		$reflection = new ReflectionClass(Encoding::class);

		foreach ($reflection->getConstants() as $encoding) {
			$attachment->setEncoding($encoding);
			$this->assertEquals($encoding, $attachment->getEncoding());
		}

		// Incompatible encoding
		try {
			$attachment->setEncoding('dummy-encoding');
			$this->fail(sprintf('Expected exception %s.', InvalidArgumentException::class));
		} catch (AssertionFailedError $e) {
			throw $e;
		} catch (Throwable $e) {
			// Correctly thrown exception
			$this->assertInstanceOf(InvalidArgumentException::class, $e);
		}
	}

}
