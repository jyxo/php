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

namespace Jyxo\Rpc\Xml;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Test pro třídu \Jyxo\Rpc\Xml\Server.
 *
 * @see \Jyxo\Rpc\Xml\Server
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček <libs@jyxo.com>
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class ServerTest extends \Jyxo\Rpc\ServerTestCase
{
	/**
	 * Vrací instanci daného serveru
	 *
	 * @return \Jyxo\Rpc\Server
	 */
	protected function getServerInstance()
	{
		return \Jyxo\Rpc\Xml\Server::getInstance();
	}

	/**
	 * Vrací příponu testovaných souborů
	 *
	 * @return string
	 */
	protected function getFileExtension()
	{
		return 'xml';
	}
}
