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
 * Common HTTP response test.
 * Checks only availability in the default form, but can be easily extended with additional checks.
 *
 * Example:
 * <code>
 * new \Jyxo\Beholder\TestCase\HttpResponse('Foo', 'http://example.com/', array('body' => '/this text must be in body/m'))
 * </code>
 *
 * @category Jyxo
 * @package Jyxo\Beholder
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Kaštánek
 */
class HttpResponse extends \Jyxo\Beholder\TestCase
{
	/**
	 * Tested URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Custom tests.
	 *
	 * @var array
	 */
	private $tests;

	/**
	 * Constructor. Gets the testing URL and optional custom tests.
	 *
	 * @param string $description Test description
	 * @param string $url Tested URL
	 * @param array $tests Custom tests
	 */
	public function __construct(string $description, string $url, array $tests = [])
	{
		parent::__construct($description);

		$this->url = $url;
		$this->tests = $tests;
	}

	/**
	 * Performs the test.
	 *
	 * @return \Jyxo\Beholder\Result
	 */
	public function run(): \Jyxo\Beholder\Result
	{
		// The \GuzzleHttp library is required
		if (!class_exists(\GuzzleHttp\Client::class)) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::NOT_APPLICABLE, 'Guzzle library missing');
		}

		try {

			$httpClient = new \GuzzleHttp\Client();
			$httpRequest = new \GuzzleHttp\Psr7\Request('GET', $this->url, ['User-Agent' => 'JyxoBeholder']);
			$httpResponse = $httpClient->send($httpRequest, [
				\GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 5,
				\GuzzleHttp\RequestOptions::TIMEOUT => 10
			]);

			if (200 !== $httpResponse->getStatusCode()) {
				throw new \Exception(sprintf('Http error: %s', $httpResponse->getReasonPhrase()));
			}
			if (isset($this->tests['body'])) {
				$body = (string) $httpResponse->getBody();
				if (strpos($body, $this->tests['body']) === false) {
					$body = trim(strip_tags($body));
					throw new \Exception(sprintf('Invalid body: %s', \Jyxo\StringUtil::cut($body, 128)));
				}
			}

			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::SUCCESS, $this->url);

		} catch (\Exception $e) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, $e->getMessage());
		}
	}
}
