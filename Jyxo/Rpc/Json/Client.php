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
 * Třída pro odesílání požadavků přes JSON-RPC.
 * Vyžaduje rozšíření json a curl.
 *
 * @category Jyxo
 * @package Jyxo\Rpc
 * @subpackage Json
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček <libs@jyxo.com>
 */
class Client extends \Jyxo\Rpc\Client
{
	/**
	 * Odešle požadavek a získá ze serveru odpověď.
	 *
	 * @param string $method
	 * @param array $params
	 * @return mixed
	 * @throws \BadMethodCallException Pokud nebyla zadána url serveru
	 * @throws \Jyxo\Rpc\Json\Exception Při chybě
	 */
	public function send($method, array $params)
	{
		// Začátek profilování
		$this->profileStart();

		try {
			// Připravíme si JSON-RPC požadavek
			$data = json_encode(
				array(
					'request' => array(
						'method' => $method,
						'params' => $params
					)
				)
			);

			// Získání odpovědi
			$response = $this->process('application/json', $data);

			// Zpracování odpovědi
			$response = json_decode($response, true);

		} catch (\Jyxo\Rpc\Exception $e) {
			// Konec profilování
			$this->profileEnd('JSON', $method, $params, $e->getMessage());

			throw new \Jyxo\Rpc\Json\Exception($e->getMessage(), 0, $e);
		}

		// Konec profilování
		$this->profileEnd('JSON', $method, $params, $response);

		// Chyba v odpovědi
		if (!is_array($response) || !isset($response['response'])) {
			throw new \Jyxo\Rpc\Json\Exception('Nebyl navrácen požadovaný formát dat.');
		}
		$response = $response['response'];
		if ((is_array($response)) && (isset($response['fault']))) {
			throw new \Jyxo\Rpc\Json\Exception(preg_replace('~\s+~', ' ', $response['fault']['faultString']));
		}

		return $response;
	}
}
