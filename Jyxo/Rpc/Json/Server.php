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
 * Třída pro vytvoření JSON-RPC serveru.
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
	 * Převod kódů chyb na textové vyjádření
	 *
	 * @var array
	 */
	private static $jsonErrors = array(
		JSON_ERROR_DEPTH => 'Maximum stack depth exceeded.',
		JSON_ERROR_CTRL_CHAR => 'Unexpected control character found.',
		JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON.'
	);

	/**
	 * Seznam zaregistrovaných metod.
	 *
	 * @var array
	 */
	private $methods = array();

	/**
	 * Skutečně zaregistruje funkci.
	 *
	 * @param string $func
	 */
	protected function register($func)
	{
		$this->methods[] = $func;
	}

	/**
	 * Zpracuje požadavek a odešle JSON-RPC odpověď.
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

			// Chyba při dekódování požadavku
			if ($data === null && ($faultCode = json_last_error()) != JSON_ERROR_NONE) {
				throw new \Jyxo\Rpc\Json\Exception(self::$jsonErrors[$faultCode], $faultCode);
			}

			// Chyba při parsování dat
			if (empty($data['request']['method'])) {
				throw new \Jyxo\Rpc\Json\Exception('Parse error.', 10);
			}

			// Neexistující metoda
			if (!in_array($data['request']['method'], $this->methods)) {
				throw new \Jyxo\Rpc\Json\Exception('Server error. Method not found.', -32601);
			}

			// Zpracování požadavku
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
