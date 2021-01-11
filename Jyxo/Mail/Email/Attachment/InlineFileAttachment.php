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
use function file_get_contents;

/**
 * Inline mail attachment created from a file.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class InlineFileAttachment extends Attachment
{

	/**
	 * Type.
	 *
	 * @var string
	 */
	protected $disposition = Attachment::DISPOSITION_INLINE;

	/**
	 * Creates an attachment.
	 *
	 * @param string $path Filename
	 * @param string $name Attachment name
	 * @param string $cid Id
	 * @param string $mimeType Attachment mime-type
	 */
	public function __construct(string $path, string $name, string $cid, string $mimeType = 'application/octet-stream')
	{
		$this->setContent(file_get_contents($path));
		$this->setName($name);
		$this->setCid($cid);
		$this->setMimeType($mimeType);
	}

	/**
	 * Sets Id.
	 *
	 * @param string $cid Id
	 * @return FileAttachment
	 */
	public function setCid(string $cid): self
	{
		$this->cid = $cid;

		return $this;
	}

}
