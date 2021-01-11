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

namespace Jyxo\Rpc\Json;

use function is_array;
use function json_decode;
use function json_encode;
use function md5;
use function preg_replace;
use function rand;
use function uniqid;

/**
 * Class for sending requests using JSON-RPC.
 * Requires json and curl PHP extensions.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 */
class Client extends \Jyxo\Rpc\Client
{

	/**
	 * Sends a request and fetches server's response.
	 *
	 * @param string $method Method name
	 * @param array $params Method parameters
	 * @return mixed
	 */
	public function send(string $method, array $params)
	{
		// Start profiling
		$this->profileStart();

		// Generates ID
		$id = md5(uniqid((string) rand(), true));

		try {
			// Prepare JSON-RPC request
			$data = json_encode(
				[
					'method' => $method,
					'params' => $params,
					'id' => $id,
				]
			);

			// Fetch response
			$response = $this->process('application/json', $data);

			// Process response
			$response = json_decode($response, true);

		} catch (\Jyxo\Rpc\Exception $e) {
			// Finish profiling
			$this->profileEnd('JSON', $method, $params, $e->getMessage());

			throw new Exception($e->getMessage(), 0, $e);
		}

		// Finish profiling
		$this->profileEnd('JSON', $method, $params, $response);

		// Error in response
		if (!is_array($response) || !isset($response['id'])) {
			throw new Exception('Invalid response data.');
		}

		if ($id !== $response['id']) {
			throw new Exception('Response ID does not correspond to request ID.');
		}

		if (isset($response['error'])) {
			throw new Exception(preg_replace('~\\s+~', ' ', $response['error']['message']), $response['error']['code']);
		}

		if (!isset($response['result'])) {
			throw new Exception('No response data.');
		}

		return $response['result'];
	}

}
