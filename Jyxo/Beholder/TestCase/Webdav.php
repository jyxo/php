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
use Jyxo\Webdav\Client;
use Jyxo\Webdav\Exception;
use Jyxo\Webdav\FileNotCreatedException;
use Jyxo\Webdav\FileNotDeletedException;
use Jyxo\Webdav\FileNotExistException;
use function class_exists;
use function filter_var;
use function gethostbyaddr;
use function md5;
use function parse_url;
use function preg_match;
use function sprintf;
use function strlen;
use function time;
use function trim;
use function uniqid;
use const FILTER_VALIDATE_IP;

/**
 * Tests WebDAV availability.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Webdav extends TestCase
{

	/**
	 * Server hostname.
	 *
	 * @var string
	 */
	private $server;

	/**
	 * Tested directory.
	 *
	 * @var string
	 */
	private $dir;

	/**
	 * Connection options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param string $server Server hostname
	 * @param string $dir Tested directory
	 * @param array $options Connection options
	 */
	public function __construct(string $description, string $server, string $dir = '', array $options = [])
	{
		parent::__construct($description);

		$this->server = $server;
		$this->dir = $dir;
		$this->options = $options;
	}

	/**
	 * Performs the test.
	 *
	 * @return Result
	 */
	public function run(): Result
	{
		// The \Jyxo\Webdav\Client class is required
		if (!class_exists(Client::class)) {
			return new Result(Result::NOT_APPLICABLE, sprintf('Class %s missing', Client::class));
		}

		$random = md5(uniqid((string) time(), true));
		$dir = trim($this->dir, '/');

		if (!empty($dir)) {
			$dir = '/' . $dir;
		}

		$path = $dir . '/beholder-' . $random . '.txt';
		$content = $random;

		// Status label
		$serverUrl = $this->server;

		if (!preg_match('~^https?://~', $this->server)) {
			$serverUrl = 'http://' . $serverUrl;
		}

		$parsed = parse_url($serverUrl);
		$host = $parsed['host'];
		$port = !empty($parsed['port']) ? $parsed['port'] : 80;
		$description = (filter_var($host, FILTER_VALIDATE_IP) !== false ? gethostbyaddr($host) : $host) . ':' . $port . $dir;

		try {
			$webdav = new Client([$serverUrl]);

			foreach ($this->options as $name => $value) {
				$webdav->setRequestOption($name, $value);
			}

			// Writing
			$webdav->put($path, $content);

			// Exists
			if (!$webdav->exists($path)) {
				return new Result(Result::FAILURE, sprintf('Exists error %s', $description));
			}

			// Reading
			$readContent = $webdav->get($path);

			if (strlen($readContent) !== strlen($content)) {
				return new Result(Result::FAILURE, sprintf('Read error %s', $description));
			}

			// Deleting
			$webdav->unlink($path);
		} catch (FileNotCreatedException $e) {
			return new Result(Result::FAILURE, sprintf('Write error %s', $description));
		} catch (FileNotExistException $e) {
			return new Result(Result::FAILURE, sprintf('Read error %s', $description));
		} catch (FileNotDeletedException $e) {
			return new Result(Result::FAILURE, sprintf('Delete error %s', $description));
		} catch (Exception $e) {
			return new Result(Result::FAILURE, sprintf('Error %s', $description));
		}

		// OK
		return new Result(Result::SUCCESS, $description);
	}

}
