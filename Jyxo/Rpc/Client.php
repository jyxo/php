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

namespace Jyxo\Rpc;

use BadMethodCallException;
use Jyxo\FirePhp;
use Jyxo\Timer;
use function count;
use function curl_close;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function sprintf;
use function strlen;
use function strtoupper;
use const CURLINFO_HTTP_CODE;
use const CURLOPT_CONNECTTIMEOUT;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_TIMEOUT;
use const CURLOPT_URL;

/**
 * Abstract class for sending RPC requests.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
abstract class Client
{

	/**
	 * Server address.
	 *
	 * @var string
	 */
	protected $url = '';

	/**
	 * Time limit for communication with RPC server (seconds).
	 *
	 * @var int
	 */
	protected $timeout = 5;

	/**
	 * Parameters for creating RPC requests.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Parameters for curl_setopt.
	 *
	 * @var array
	 */
	private $curlOptions = [];

	/**
	 * Timer name.
	 *
	 * @var string
	 */
	private $timer = '';

	/**
	 * Whether to use request profiler.
	 *
	 * @var bool
	 */
	private $profiler = false;

	/**
	 * Sends a request and fetches a response from the server.
	 *
	 * @param string $method Method name
	 * @param array $params Method parameters
	 * @return mixed
	 */
	abstract public function send(string $method, array $params);

	/**
	 * Creates client instance and eventually sets server address.
	 *
	 * @param string $url Server address
	 */
	public function __construct(string $url = '')
	{
		if (!empty($url)) {
			$this->setUrl($url);
		}
	}

	/**
	 * Sets server address.
	 *
	 * @param string $url Server address
	 * @return Client
	 */
	public function setUrl(string $url): self
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * Sets timeout.
	 *
	 * @param int $timeout Call timeout
	 * @return Client
	 */
	public function setTimeout(int $timeout): self
	{
		$this->timeout = $timeout;

		return $this;
	}

	/**
	 * Changes client settings.
	 *
	 * @param string $key Parameter name
	 * @param mixed $value Parameter value
	 * @return Client
	 */
	public function setOption(string $key, $value): self
	{
		if (isset($this->options[$key])) {
			$this->options[$key] = $value;
		}

		return $this;
	}

	/**
	 * Returns certain parameter or whole array of parameters if no parameter name is provided.
	 *
	 * @param string $key Parameter name
	 * @return mixed
	 */
	public function getOption(string $key = '')
	{
		if (isset($this->options[$key])) {
			return $this->options[$key];
		}

		return $this->options;
	}

	/**
	 * Changes curl_setopt settings.
	 *
	 * @param string $key Parameter name
	 * @param mixed $value Parameter value
	 * @return Client
	 */
	public function setCurlOption(string $key, $value): self
	{
		$this->curlOptions[$key] = $value;

		return $this;
	}

	/**
	 * Returns certain curl_setopt parameter or whole array of parameters if no parameter name is provided.
	 *
	 * @param string $key Parameter name
	 * @return mixed
	 */
	public function getCurlOption(string $key = '')
	{
		if (isset($this->curlOptions[$key])) {
			return $this->curlOptions[$key];
		}

		return $this->curlOptions;
	}

	/**
	 * Turns request profiler on.
	 *
	 * @return Client
	 */
	public function enableProfiler(): self
	{
		$this->profiler = true;

		return $this;
	}

	/**
	 * Turns request profiler off.
	 *
	 * @return Client
	 */
	public function disableProfiler(): self
	{
		$this->profiler = false;

		return $this;
	}

	/**
	 * Processes request data and fetches response.
	 *
	 * @param string $contentType Request content-type
	 * @param string $data Request data
	 * @return string
	 */
	protected function process(string $contentType, string $data): string
	{
		// Server address must be defined
		if (empty($this->url)) {
			throw new BadMethodCallException('No server address was provided.');
		}

		// Headers
		$headers = [
			'Content-Type: ' . $contentType,
			'Content-Length: ' . strlen($data),
		];

		$defaultCurlOptions = [
			CURLOPT_URL => $this->url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 0,
			CURLOPT_TIMEOUT => $this->timeout,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POSTFIELDS => $data,
		];

		$curlOptions = $this->curlOptions + $defaultCurlOptions;

		// Open a HTTP channel
		$channel = curl_init();

		foreach ($curlOptions as $key => $value) {
			curl_setopt($channel, $key, $value);
		}

		// Send a request
		$response = curl_exec($channel);

		// Error sending the request
		if (curl_errno($channel) !== 0) {
			$error = curl_error($channel);
			curl_close($channel);

			throw new Exception($error);
		}

		// Wrong code
		$code = curl_getinfo($channel, CURLINFO_HTTP_CODE);

		if ($code >= 300) {
			$error = sprintf('Response error from %s, code %d.', $this->url, $code);
			curl_close($channel);

			throw new Exception($error);
		}

		// Close the channel
		curl_close($channel);

		// Return the response
		return $response;
	}

	/**
	 * Starts profiling.
	 *
	 * @return Client
	 */
	protected function profileStart(): self
	{
		// Set start time
		if ($this->profiler) {
			$this->timer = Timer::start();
		}

		return $this;
	}

	/**
	 * Finishes profiling.
	 *
	 * @param string $type Request type
	 * @param string $method Method name
	 * @param array $params Method parameters
	 * @param mixed $response Server response
	 * @return Client
	 */
	protected function profileEnd(string $type, string $method, array $params, $response): self
	{
		// Profiling has to be turned on
		if ($this->profiler) {
			static $totalTime = 0;
			static $requests = [];

			// Get elapsed time
			$time = Timer::stop($this->timer);

			$totalTime += $time;
			$requests[] = [strtoupper($type), (string) $method, $params, $response, sprintf('%0.3f', $time * 1000)];

			// Send to FirePHP
			FirePhp::table(
				sprintf('Jyxo RPC Profiler (%d requests took %0.3f ms)', count($requests), sprintf('%0.3f', $totalTime * 1000)),
				['Type', 'Method', 'Request', 'Response', 'Time'],
				$requests,
				'Rpc'
			);
		}

		return $this;
	}

}
