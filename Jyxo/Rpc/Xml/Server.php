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

use function file_get_contents;
use function header;
use function is_resource;
use function xmlrpc_server_call_method;
use function xmlrpc_server_create;
use function xmlrpc_server_destroy;
use function xmlrpc_server_register_method;

/**
 * Class for creating a XML-RPC server.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Server extends \Jyxo\Rpc\Server
{

	/**
	 * Server instance.
	 *
	 * @var resource
	 */
	private $server = null;

	/**
	 * Creates a class instance.
	 */
	protected function __construct()
	{
		parent::__construct();

		$this->server = xmlrpc_server_create();
	}

	/**
	 * Destroys a class instance.
	 */
	public function __destruct()
	{
		parent::__destruct();

		if (is_resource($this->server)) {
			xmlrpc_server_destroy($this->server);
		}
	}

	/**
	 * Processes a request and sends a XML-RPC response.
	 */
	public function process(): void
	{
		$options = [
			'output_type' => 'xml',
			'verbosity' => 'pretty',
			'escaping' => ['markup'],
			'version' => 'xmlrpc',
			'encoding' => 'utf-8',
		];

		$response = xmlrpc_server_call_method($this->server, file_get_contents('php://input'), null, $options);
		header('Content-Type: text/xml; charset="utf-8"');
		echo $response;
	}

	/**
	 * Actually registers a function to a server method.
	 *
	 * @param string $func Function definition
	 */
	protected function register(string $func): void
	{
		xmlrpc_server_register_method($this->server, $func, [$this, 'call']);
	}

}
