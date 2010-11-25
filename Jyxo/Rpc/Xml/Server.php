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

/**
 * Třída pro vytvoření XML-RPC serveru.
 *
 * @category Jyxo
 * @package Jyxo\Rpc
 * @subpackage Xml
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class Server extends \Jyxo\Rpc\Server
{
	/**
	 * Instance serveru.
	 *
	 * @var resource
	 */
	private $server = null;

	/**
	 * Vytvoří instanci třídy.
	 */
	protected function __construct()
	{
		parent::__construct();
		$this->server = xmlrpc_server_create();
	}

	/**
	 * Zruší instanci třídy.
	 */
	public function __destruct()
	{
		parent::__destruct();
		if (is_resource($this->server)) {
			xmlrpc_server_destroy($this->server);
		}
	}

	/**
	 * Skutečně zaregistruje funkci.
	 *
	 * @param string $func
	 */
	protected function register($func)
	{
		xmlrpc_server_register_method($this->server, $func, array($this, 'call'));
	}

	/**
	 * Zpracuje požadavek a odešle XML-RPC odpověď.
	 */
	public function process()
	{
		$options = array(
			'output_type' => 'xml',
			'verbosity' => 'pretty',
			'escaping' => array('markup'),
			'version' => 'xmlrpc',
			'encoding' => 'utf-8'
		);

		$response = xmlrpc_server_call_method($this->server, file_get_contents('php://input'), null, $options);
		header('Content-Type: text/xml; charset="utf-8"');
		echo $response;
	}
}
