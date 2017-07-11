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

namespace Jyxo\Beholder\Output;

class NoOutput extends \Jyxo\Beholder\Output\Output
{

	public function getContentType(): string
	{
		return 'text/plain; charset=utf-8';
	}

	public function __toString(): string
	{
		return '';
	}
}
