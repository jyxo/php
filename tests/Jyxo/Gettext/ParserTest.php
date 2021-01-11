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

namespace Jyxo\Gettext;

use Jyxo\Gettext\Parser\Item;
use PHPUnit\Framework\TestCase;

/**
 * Test for class \Jyxo\Gettext\Parser
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Matěj Humpál
 */
class ParserTest extends TestCase
{

	public function testParse(): void
	{
		$po = new Parser(__DIR__ . '/../../files/gettext/patterns.cs.po');
		$this->assertCount(300, $po);

		foreach ($po as $item) {
			$this->assertInstanceOf(Item::class, $item);
		}
	}

}
