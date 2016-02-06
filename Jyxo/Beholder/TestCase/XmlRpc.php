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

namespace Jyxo\Beholder\TestCase;

/**
 * Tests XML-RPC server availability.
 *
 * @category Jyxo
 * @package Jyxo\Beholder
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class XmlRpc extends \Jyxo\Beholder\TestCase
{
	/**
	 * XML-RPC server URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Method name.
	 *
	 * @var string
	 */
	private $method;

	/**
	 * Method parameters.
	 *
	 * @var string
	 */
	private $params = [];

	/**
	 * Request options.
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Timeout.
	 *
	 * @var integer
	 */
	private $timeout;

	/**
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param string $url Server URL
	 * @param string $method Method name
	 * @param array $params Method parameters
	 * @param array $options Request options
	 * @param integer $timeout Timeout
	 */
	public function __construct(string $description, string $url, string $method, array $params, array $options = [], int $timeout = 2)
	{
		parent::__construct($description);

		$this->url = $url;
		$this->method = $method;
		$this->params = $params;
		$this->options = $options;
		$this->timeout = $timeout;
	}

	/**
	 * Performs the test.
	 *
	 * @return \Jyxo\Beholder\Result
	 */
	public function run(): \Jyxo\Beholder\Result
	{
		// The xmlrpc extension is required
		if (!extension_loaded('xmlrpc')) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::NOT_APPLICABLE, 'Extension xmlrpc missing');
		}

		// The curl extension is required
		if (!extension_loaded('curl')) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::NOT_APPLICABLE, 'Extension curl missing');
		}

		// The \Jyxo\Rpc\Xml\Client class is required
		if (!class_exists(\Jyxo\Rpc\Xml\Client::class)) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::NOT_APPLICABLE, sprintf('Class %s missing', \Jyxo\Rpc\Xml\Client::class));
		}

		// Create the RPC client
		$rpc = new \Jyxo\Rpc\Xml\Client();
		foreach ($this->options as $name => $value) {
			$rpc->setOption($name, $value);
		}
		$rpc->setUrl($this->url)
			->setTimeout($this->timeout);

		// Send the request
		try {
			$rpc->send($this->method, $this->params);
		} catch (\Exception $e) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, $this->url);
		}

		// OK
		return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::SUCCESS, $this->url);
	}
}
