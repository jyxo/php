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

use Jyxo\Beholder\Result;
use Jyxo\Beholder\TestCase;
use Jyxo\Rpc\Json\Client;
use Throwable;
use function class_exists;
use function extension_loaded;
use function sprintf;

/**
 * Tests JSON-RPC server availability.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 * @author Jaroslav Hanslík
 */
class JsonRpc extends TestCase
{

	/**
	 * JSON-RPC server URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Tested method.
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
	 * Timeout.
	 *
	 * @var int
	 */
	private $timeout;

	/**
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param string $url Server URL
	 * @param string $method Tested method
	 * @param array $params Method parameters
	 * @param int $timeout Timeout
	 */
	public function __construct(string $description, string $url, string $method, array $params, int $timeout = 2)
	{
		parent::__construct($description);

		$this->url = $url;
		$this->method = $method;
		$this->params = $params;
		$this->timeout = $timeout;
	}

	/**
	 * Performs the test.
	 *
	 * @return Result
	 */
	public function run(): Result
	{
		// The json extension is required
		if (!extension_loaded('json')) {
			return new Result(Result::NOT_APPLICABLE, 'Extension json missing');
		}

		// The curl extension is required
		if (!extension_loaded('curl')) {
			return new Result(Result::NOT_APPLICABLE, 'Extension curl missing');
		}

		// The \Jyxo\Rpc\Json\Client class is required
		if (!class_exists(Client::class)) {
			return new Result(
				Result::NOT_APPLICABLE,
				sprintf('Class %s missing', Client::class)
			);
		}

		// Creates a client
		$rpc = new Client();
		$rpc->setUrl($this->url)
			->setTimeout($this->timeout);

		// Sends the request
		try {
			$rpc->send($this->method, $this->params);
		} catch (Throwable $e) {
			return new Result(Result::FAILURE, $this->url);
		}

		// OK
		return new Result(Result::SUCCESS, $this->url);
	}

}
