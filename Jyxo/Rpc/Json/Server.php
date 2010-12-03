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

namespace Jyxo\Rpc\Json;

/**
 * Class for creating a JSON-RPC server.
 *
 * @category Jyxo
 * @package Jyxo\Rpc
 * @subpackage Json
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček <libs@jyxo.com>
 */
class Server extends \Jyxo\Rpc\Server
{
	/**
	 * Definition of error codes and appropriate error messages.
	 *
	 * @var array
	 */
	private static $jsonErrors = array(
		JSON_ERROR_DEPTH => 'Maximum stack depth exceeded.',
		JSON_ERROR_CTRL_CHAR => 'Unexpected control character found.',
		JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON.'
	);

	/**
	 * List of registered methods.
	 *
	 * @var array
	 */
	private $methods = array();

	/**
	 * Actually registers a function to a server method.
	 *
	 * @param string $func Function definition
	 */
	protected function register($func)
	{
		$this->methods[] = $func;
	}

	/**
	 * Processes a request and sends a JSON-RPC response.
	 */
	public function process()
	{
		try {
			$data = file_get_contents('php://input');
			$data = trim($data);
			if (empty($data)) {
				throw new \Jyxo\Rpc\Json\Exception('No data received.', -32700);
			}
			$data = json_decode($data, true);

			// Request decoding error
			if ($data === null && ($faultCode = json_last_error()) != JSON_ERROR_NONE) {
				throw new \Jyxo\Rpc\Json\Exception(self::$jsonErrors[$faultCode], $faultCode);
			}

			// Parsing request data error
			if (empty($data['request']['method'])) {
				throw new \Jyxo\Rpc\Json\Exception('Parse error.', 10);
			}

			// Non-existent method call
			if (!in_array($data['request']['method'], $this->methods)) {
				throw new \Jyxo\Rpc\Json\Exception('Server error. Method not found.', -32601);
			}

			// Request processing
			$params = !empty($data['request']['params']) ? $data['request']['params'] : array();
			$response = $this->call($data['request']['method'], $params);
			$response = array('response' => $response);

		} catch (\Jyxo\Rpc\Json\Exception $e) {
			$response = array(
				'response' => array(
					'fault' => array(
						'faultString' => $e->getMessage(),
						'faultCode' => $e->getCode()
					)
				)
			);
		}

		header('Content-Type: application/json; charset="utf-8"');
		echo json_encode($response);
	}
}
