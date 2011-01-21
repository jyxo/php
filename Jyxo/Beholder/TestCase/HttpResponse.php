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

namespace Jyxo\Beholder\TestCase;

/**
 * Common HTTP response test.
 * Checks only availability in the default form, but can be easily extended with additional checks.
 *
 * Example:
 * new \Jyxo\Beholder\TestCase\HttpResponse('Foo', 'http://example.com/', array('body' => '/this text must be in body/m'))
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
	public function __construct($description, $url, array $tests = array())
	{
		parent::__construct($description);

		$this->url = (string) $url;
		$this->tests = $tests;
	}

	/**
	 * Performs the test.
	 *
	 * @return \Jyxo\Beholder\Result
	 */
	public function run()
	{
		// The http extension is required
		if (!extension_loaded('http')) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::NOT_APPLICABLE, 'Extension http missing');
		}

		$http = new \HttpRequest(
			$this->url, \HttpRequest::METH_GET, array(
				'connecttimeout' => 5, 'timeout' => 10, 'useragent' => 'JyxoBeholder'
			)
		);

		try {
			$http->send();
			if (200 !== $http->getResponseCode()) {
				throw new \Exception(sprintf('Http error: %s', $http->getResponseCode()));
			}
			if (isset($this->tests['body'])) {
				$body = $http->getResponseBody();
				if (!preg_match($this->tests['body'], $body)) {
					$body = trim(strip_tags($body));
					throw new \Exception(sprintf('Invalid body: %s', \Jyxo\String::cut($body, 16)));
				}
			}

			// OK
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::SUCCESS);

		} catch (\HttpException $e) {
			$inner = $e;
			while (null !== $inner->innerException) {
				$inner = $inner->innerException;
			}
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, $inner->getMessage());

		} catch (\Exception $e) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, $e->getMessage());
		}
	}
}
