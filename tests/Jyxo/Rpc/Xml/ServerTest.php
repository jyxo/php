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

namespace Jyxo\Rpc\Xml;

use Jyxo\Rpc\ServerTestCase;

/**
 * Test for class \Jyxo\Rpc\Xml\Server.
 *
 * @see \Jyxo\Rpc\Xml\Server
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 * @author Jaroslav Hanslík
 */
class ServerTest extends ServerTestCase
{

	/**
	 * Returns server instance.
	 *
	 * @return \Jyxo\Rpc\Server
	 */
	protected function getServerInstance(): \Jyxo\Rpc\Server
	{
		return Server::getInstance();
	}

	/**
	 * Returns test files extension.
	 *
	 * @return string
	 */
	protected function getFileExtension(): string
	{
		return 'xml';
	}

}
