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

use function is_array;
use function preg_replace;
use function xmlrpc_decode;
use function xmlrpc_encode_request;

/**
 * Class for sending requests using XML-RPC.
 * Requires xmlrpc and curl PHP extensions.
 *
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
	public function __construct(string $url = '')
	{
		parent::__construct($url);

		$this->options = [
			'output_type' => 'xml',
			'verbosity' => 'pretty',
			'escaping' => ['markup'],
			'version' => 'xmlrpc',
			'encoding' => 'utf-8',
		];
	}

	/**
	 * Sends request and fetches server's response.
	 *
	 * @param string $method Method name
	 * @param array $params Method parameters
	 * @return mixed
	 */
	public function send(string $method, array $params)
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

			throw new Exception($e->getMessage(), 0, $e);
		}

		// Finish profiling
		$this->profileEnd('XML', $method, $params, $response);

		// Error in response
		if (is_array($response) && (isset($response['faultString']))) {
			throw new Exception(preg_replace('~\\s+~', ' ', $response['faultString']));
		}

		return $response;
	}

}
