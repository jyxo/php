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
 * Třída pro odesílání požadavků přes XML-RPC.
 * Vyžaduje rozšíření xmlrpc a curl.
 *
 * @category Jyxo
 * @package Jyxo\Rpc
 * @subpackage Xml
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class Client extends \Jyxo\Rpc\Client
{
	/**
	 * Vytvoří instanci klienta a případně nastaví adresu serveru.
	 * Také nastaví výchozí nastavení klienta.
	 *
	 * @param string $url
	 */
	public function __construct($url = '')
	{
		parent::__construct($url);

		$this->options = array(
			'output_type' => 'xml',
			'verbosity' => 'pretty',
			'escaping' => array('markup'),
			'version' => 'xmlrpc',
			'encoding' => 'utf-8'
		);
	}

	/**
	 * Odešle požadavek a získá ze serveru odpověď.
	 *
	 * @param string $method
	 * @param array $params
	 * @return mixed
	 * @throws \BadMethodCallException Pokud nebyla zadána url serveru
	 * @throws \Jyxo\Rpc\Xml\Exception Při chybě
	 */
	public function send($method, array $params)
	{
		// Začátek profilování
		$this->profileStart();

		try {
			// Získání odpovědi
			$response = $this->process('text/xml', xmlrpc_encode_request($method, $params, $this->options));

			// Zpracování odpovědi
			$response = xmlrpc_decode($response, 'utf-8');

		} catch (\Jyxo\Rpc\Exception $e) {
			// Konec profilování
			$this->profileEnd('XML', $method, $params, $e->getMessage());

			throw new \Jyxo\Rpc\Xml\Exception($e->getMessage(), 0, $e);
		}

		// Konec profilování
		$this->profileEnd('XML', $method, $params, $response);

		// Chyba v odpovědi
		if ((is_array($response)) && (isset($response['faultString']))) {
			throw new \Jyxo\Rpc\Xml\Exception(preg_replace('~\s+~', ' ', $response['faultString']));
		}

		return $response;
	}
}
