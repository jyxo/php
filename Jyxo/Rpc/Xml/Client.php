<?php

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

/**
 * Class for sending requests using XML-RPC.
 * Requires xmlrpc and curl PHP extensions.
 *
 * @category Jyxo
 * @package Jyxo\Rpc
 * @subpackage Xml
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Client extends \Jyxo\Rpc\Client
{
	/**
	 * Creates a client instance and eventually sets server address.
	 * Also defines default client settings.
	 *
	 * @param string $url Server address
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
	 * Sends request and fetches server's response.
	 *
	 * @param string $method Method name
	 * @param array $params Method parameters
	 * @return mixed
	 * @throws \BadMethodCallException If no server address was provided
	 * @throws \Jyxo\Rpc\Xml\Exception On error
	 */
	public function send($method, array $params)
	{
		// Start profiling
		$this->profileStart();

		try {
			// Fetch response
			$response = $this->process('text/xml', xmlrpc_encode_request($method, $params, $this->options));

			// Process response
			$response = xmlrpc_decode($response, 'utf-8');

		} catch (\Jyxo\Rpc\Exception $e) {
			// Finish profiling
			$this->profileEnd('XML', $method, $params, $e->getMessage());

			throw new \Jyxo\Rpc\Xml\Exception($e->getMessage(), 0, $e);
		}

		// Finish profiling
		$this->profileEnd('XML', $method, $params, $response);

		// Error in response
		if ((is_array($response)) && (isset($response['faultString']))) {
			throw new \Jyxo\Rpc\Xml\Exception(preg_replace('~\s+~', ' ', $response['faultString']));
		}

		return $response;
	}
}
