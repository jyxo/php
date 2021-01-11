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

namespace Jyxo\Webdav;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use XMLReader;
use function array_rand;
use function dirname;
use function explode;
use function fopen;
use function preg_match;
use function rtrim;
use function sprintf;
use function trim;

/**
 * Client for work with WebDav. Uses the http PHP extension.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Client
{

	public const METHOD_HEAD = 'HEAD';

	public const METHOD_GET = 'GET';

	public const METHOD_PUT = 'PUT';

	public const METHOD_DELETE = 'DELETE';

	public const METHOD_COPY = 'COPY';

	public const METHOD_MOVE = 'MOVE';

	public const METHOD_MKCOL = 'MKCOL';

	public const METHOD_PROPFIND = 'PROPFIND';

	public const STATUS_200_OK = 200;

	public const STATUS_201_CREATED = 201;

	public const STATUS_204_NO_CONTENT = 204;

	public const STATUS_207_MULTI_STATUS = 207;

	public const STATUS_403_FORBIDDEN = 403;

	public const STATUS_404_NOT_FOUND = 404;

	public const STATUS_405_METHOD_NOT_ALLOWED = 405;

	public const STATUS_409_CONFLICT = 409;

	/**
	 * Servers list.
	 *
	 * @var array
	 */
	protected $servers = [];

	/**
	 * Request options.
	 *
	 * @var array
	 */
	protected $requestOptions = [
		RequestOptions::ALLOW_REDIRECTS => false,
		RequestOptions::DECODE_CONTENT => true,
		RequestOptions::CONNECT_TIMEOUT => 1,
		RequestOptions::TIMEOUT => 30,
		RequestOptions::VERIFY => true,
	];

	/**
	 * Logger.
	 *
	 * @var LoggerInterface
	 */
	protected $logger = null;

	/**
	 * If parallel request sending is enabled.
	 *
	 * @var bool
	 */
	protected $parallelSending = true;

	/**
	 * If directories should be created automatically.
	 *
	 * If disabled, commands will throw an error if target directory doesn't exist.
	 *
	 * @var bool
	 */
	protected $createDirectoriesAutomatically = true;

	/**
	 * Constructor.
	 *
	 * @param array $servers
	 */
	public function __construct(array $servers)
	{
		$this->servers = $servers;
	}

	/**
	 * Sets a request option for Guzzle client.
	 *
	 * @see \GuzzleHttp\RequestOptions
	 * @param string $name Option name
	 * @param mixed $value Option value
	 */
	public function setRequestOption(string $name, $value): self
	{
		$this->requestOptions[$name] = $value;

		return $this;
	}

	/**
	 * Sets a logger.
	 *
	 * @param LoggerInterface $logger Logger
	 * @return Client
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * Enables/disables parallel request sending.
	 *
	 * @param bool $parallelSending
	 * @return Client
	 */
	public function setParallelSending(bool $parallelSending): self
	{
		$this->parallelSending = $parallelSending;

		return $this;
	}

	/**
	 * Enables/disables automatic creation of target directories.
	 *
	 * @param bool $createDirectoriesAutomatically
	 * @return Client
	 */
	public function setCreateDirectoriesAutomatically(bool $createDirectoriesAutomatically): self
	{
		$this->createDirectoriesAutomatically = $createDirectoriesAutomatically;

		return $this;
	}

	/**
	 * Checks if a file exists.
	 *
	 * @param string $path File path
	 * @return bool
	 */
	public function exists(string $path): bool
	{
		$response = $this->sendRequest($this->getFilePath($path), self::METHOD_HEAD);

		return $response->getStatusCode() === self::STATUS_200_OK;
	}

	/**
	 * Returns file contents.
	 *
	 * @param string $path File path
	 * @return string
	 */
	public function get(string $path): string
	{
		// Asking random server
		$path = $this->getFilePath($path);
		$response = $this->sendRequest($path, self::METHOD_GET);

		if ($response->getStatusCode() !== self::STATUS_200_OK) {
			throw new FileNotExistException(sprintf('File %s does not exist.', $path));
		}

		return (string) $response->getBody();
	}

	/**
	 * Returns a file property.
	 * If no particular property is set, all properties are returned.
	 *
	 * @param string $path File path
	 * @param string|null $property Property name
	 * @return mixed
	 */
	public function getProperty(string $path, ?string $property = null)
	{
		// Asking random server
		$path = $this->getFilePath($path);
		$response = $this->sendRequest($path, self::METHOD_PROPFIND, ['Depth' => '0']);

		if ($response->getStatusCode() !== self::STATUS_207_MULTI_STATUS) {
			throw new FileNotExistException(sprintf('File %s does not exist.', $path));
		}

		// Fetches file properties from the server
		$properties = $this->getProperties($response);

		// Returns the requested property value
		if ($property !== null && isset($properties[$property])) {
			return $properties[$property];
		}

		// Returns all properties
		return $properties;
	}

	/**
	 * Saves data to a remote file.
	 *
	 * @param string $path File path
	 * @param string $data Data
	 */
	public function put(string $path, string $data): void
	{
		$this->processPut($this->getFilePath($path), $data, false);
	}

	/**
	 * Saves file contents to a remote file.
	 *
	 * @param string $path File path
	 * @param string $file Local file path
	 */
	public function putFile(string $path, string $file): void
	{
		$this->processPut($this->getFilePath($path), $file, true);
	}

	/**
	 * Copies a file.
	 * Does not work on Lighttpd.
	 *
	 * @param string $pathFrom Source file path
	 * @param string $pathTo Target file path
	 */
	public function copy(string $pathFrom, string $pathTo): void
	{
		$pathTo = $this->getFilePath($pathTo);

		// Try creating the directory first
		if ($this->createDirectoriesAutomatically) {
			try {
				$this->mkdir(dirname($pathTo));
			} catch (DirectoryNotCreatedException $e) {
				throw new FileNotCopiedException(sprintf('File %s cannot be copied to %s.', $pathFrom, $pathTo), 0, $e);
			}
		}

		$requests = [];

		foreach ($this->servers as $server) {
			$requests[$server] = $this->createRequest($server, $this->getFilePath($pathFrom), self::METHOD_COPY, [
				'Destination' => $server . $pathTo,
			]);
		}

		foreach ($this->sendAllRequests($requests) as $response) {
			// 201 means copied
			if ($response->getStatusCode() !== self::STATUS_201_CREATED) {
				throw new FileNotCopiedException(sprintf('File %s cannot be copied to %s.', $pathFrom, $pathTo));
			}
		}
	}

	/**
	 * Renames a file.
	 * Does not work on Lighttpd.
	 *
	 * @param string $pathFrom Original file name
	 * @param string $pathTo New file name
	 */
	public function rename(string $pathFrom, string $pathTo): void
	{
		$pathTo = $this->getFilePath($pathTo);

		// Try creating the directory first
		if ($this->createDirectoriesAutomatically) {
			try {
				$this->mkdir(dirname($pathTo));
			} catch (DirectoryNotCreatedException $e) {
				throw new FileNotRenamedException(sprintf('File %s cannot be renamed to %s.', $pathFrom, $pathTo), 0, $e);
			}
		}

		$requests = [];

		foreach ($this->servers as $server) {
			$requests[$server] = $this->createRequest($server, $this->getFilePath($pathFrom), self::METHOD_MOVE, [
				'Destination' => $server . $pathTo,
			]);
		}

		foreach ($this->sendAllRequests($requests) as $response) {
			switch ($response->getStatusCode()) {
				case self::STATUS_201_CREATED:
				case self::STATUS_204_NO_CONTENT:
					// Means renamed
					break;
				default:
					throw new FileNotRenamedException(sprintf('File %s cannot be renamed to %s.', $pathFrom, $pathTo));
			}
		}
	}

	/**
	 * Deletes a file.
	 * Contains a check preventing from deleting directories.
	 *
	 * @param string $path Directory path
	 */
	public function unlink(string $path): void
	{
		// We do not delete directories
		if ($this->isDir($path)) {
			throw new FileNotDeletedException(sprintf('The path %s is a directory.', $path));
		}

		foreach ($this->sendAllRequests($this->createAllRequests($this->getFilePath($path), self::METHOD_DELETE)) as $response) {
			switch ($response->getStatusCode()) {
				case self::STATUS_200_OK:
				case self::STATUS_204_NO_CONTENT:
					// Means deleted

				case self::STATUS_404_NOT_FOUND:
					break;
				default:
					throw new FileNotDeletedException(sprintf('File %s cannot be deleted.', $path));
			}
		}
	}

	/**
	 * Checks if a directory exists.
	 *
	 * @param string $dir Directory path
	 * @return bool
	 */
	public function isDir(string $dir): bool
	{
		// Asking random server
		$response = $this->sendRequest($this->getDirPath($dir), self::METHOD_PROPFIND, ['Depth' => '0']);

		// The directory does not exist or server does not support PROPFIND method
		if ($response->getStatusCode() !== self::STATUS_207_MULTI_STATUS) {
			return false;
		}

		// Fetches properties from the server
		$properties = $this->getProperties($response);

		// Checks if it is a directory
		return isset($properties['getcontenttype']) && ($properties['getcontenttype'] === 'httpd/unix-directory');
	}

	/**
	 * Creates a directory.
	 *
	 * @param string $dir Directory path
	 * @param bool $recursive Create directories recursively?
	 */
	public function mkdir(string $dir, bool $recursive = true): void
	{
		// If creating directories recursively, create the parent directory first
		$dir = trim($dir, '/');

		$dirs = $recursive ? explode('/', $dir) : [$dir];

		$path = '';

		foreach ($dirs as $dir) {
			$path .= rtrim($dir);
			$path = $this->getDirPath($path);

			foreach ($this->sendAllRequests($this->createAllRequests($path, self::METHOD_MKCOL)) as $response) {
				switch ($response->getStatusCode()) {
					// The directory was created
					case self::STATUS_201_CREATED:
						break;

					// The directory already exists

					case self::STATUS_405_METHOD_NOT_ALLOWED:
						break;

					// The directory could not be created

					default:
						throw new DirectoryNotCreatedException(sprintf('Directory %s cannot be created.', $path));
				}
			}
		}
	}

	/**
	 * Deletes a directory.
	 *
	 * @param string $dir Directory path
	 */
	public function rmdir(string $dir): void
	{
		foreach ($this->sendAllRequests($this->createAllRequests($this->getDirPath($dir), self::METHOD_DELETE)) as $response) {
			// 204 means deleted
			if ($response->getStatusCode() !== self::STATUS_204_NO_CONTENT) {
				throw new DirectoryNotDeletedException(sprintf('Directory %s cannot be deleted.', $dir));
			}
		}
	}

	/**
	 * Processes a PUT request.
	 *
	 * @param string $path File path
	 * @param string $data Data
	 * @param bool $isFile Determines if $data is a file name or actual data
	 */
	protected function processPut(string $path, string $data, bool $isFile): void
	{
		$requests = [];

		foreach ($this->servers as $server) {
			$body = $isFile ? fopen($data, 'r') : $data;
			$requests[$server] = $this->createRequest($server, $path, self::METHOD_PUT, [], $body);
		}

		$success = true;

		foreach ($this->sendAllRequests($requests) as $response) {
			switch ($response->getStatusCode()) {
				// Saved
				case self::STATUS_200_OK:
				case self::STATUS_201_CREATED:
					break;

				// An existing file was modified

				case self::STATUS_204_NO_CONTENT:
					break;

				// The directory might not exist

				case self::STATUS_403_FORBIDDEN:
				case self::STATUS_404_NOT_FOUND:
				case self::STATUS_409_CONFLICT:
					$success = false;

					break;

				// Could not save

				default:
					throw new FileNotCreatedException(sprintf('File %s cannot be created.', $path));
			}
		}

		// Saved
		if ($success) {
			return;
		}

		// Not saved, try creating the directory first
		if ($this->createDirectoriesAutomatically) {
			try {
				$this->mkdir(dirname($path));
			} catch (DirectoryNotCreatedException $e) {
				throw new FileNotCreatedException(sprintf('File %s cannot be created.', $path), 0, $e);
			}
		}

		// Try again
		foreach ($this->sendAllRequests($requests) as $response) {
			// 201 means saved
			if ($response->getStatusCode() !== self::STATUS_201_CREATED) {
				throw new FileNotCreatedException(sprintf('File %s cannot be created.', $path));
			}
		}
	}

	/**
	 * Sends requests to all servers.
	 *
	 * @param Request[] $requests Request list
	 * @return Response[]
	 */
	protected function sendAllRequests(array $requests): array
	{
		try {
			$responses = [];

			$client = $this->createClient();

			if ($this->parallelSending) {
				// Send parallel requests

				// Create promises
				$promises = [];

				foreach ($requests as $server => $request) {
					$promises[$server] = $client->sendAsync($request, $this->getStaticRequestOptions());
				}

				// Wait on all of the requests to complete
				$responses = [];

				foreach ($promises as $server => $promise) {
					$responses[$server] = $promise->wait();
				}

				// Log
				if ($this->logger !== null) {
					foreach ($responses as $server => $response) {
						$this->logger->log(
							sprintf('%s %d %s', $requests[$server]->getMethod(), $response->getStatusCode(), $requests[$server]->getUri())
						);
					}
				}
			} else {
				// Send by separate requests
				foreach ($requests as $server => $request) {
					$response = $client->send($request, $this->getStaticRequestOptions());
					$responses[$server] = $response;

					// Log
					if ($this->logger !== null) {
						$this->logger->log(sprintf('%s %d %s', $request->getMethod(), $response->getStatusCode(), $request->getUri()));
					}
				}
			}

			return $responses;
		} catch (GuzzleException $e) {
			throw new Exception($e->getMessage(), 0, $e);
		}
	}

	/**
	 * Sends a request.
	 *
	 * @param string $path Request path
	 * @param string $method Request method
	 * @param array $headers Array of headers
	 * @return Response
	 */
	protected function sendRequest(string $path, string $method, array $headers = []): Response
	{
		try {
			// Send request to a random server
			$request = $this->createRequest($this->getRandomServer(), $path, $method, $headers);
			$response = $this->createClient()->send($request, $this->getStaticRequestOptions());

			if ($this->logger !== null) {
				$this->logger->log(sprintf('%s %d %s', $request->getMethod(), $response->getStatusCode(), $request->getUri()));
			}

			return $response;
		} catch (GuzzleException $e) {
			throw new Exception($e->getMessage(), 0, $e);
		}
	}

	/**
	 * Creates a list of requests; one for each server.
	 *
	 * @param string $path Request path
	 * @param string $method Request method
	 * @param array $headers Array of headers
	 * @param string|resource|null $body Request body
	 * @return Request[]
	 */
	protected function createAllRequests(string $path, string $method, array $headers = [], $body = null): array
	{
		$requests = [];

		foreach ($this->servers as $server) {
			$requests[$server] = $this->createRequest($server, $path, $method, $headers, $body);
		}

		return $requests;
	}

	/**
	 * Creates a request.
	 *
	 * @param string $server Server
	 * @param string $path Path
	 * @param string $method Request method
	 * @param array $headers Array of headers
	 * @param string|resource|null $body Request body
	 * @return Request
	 */
	protected function createRequest(string $server, string $path, string $method, array $headers = [], $body = null): Request
	{
		return new Request(
			$method,
			rtrim($server, '/') . $path,
			$headers,
			$body
		);
	}

	protected function createClient(): \GuzzleHttp\Client
	{
		return new \GuzzleHttp\Client($this->requestOptions);
	}

	/**
	 * Creates a file path without the trailing slash.
	 *
	 * @param string $path File path
	 * @return string
	 */
	protected function getFilePath(string $path): string
	{
		return '/' . trim($path, '/');
	}

	/**
	 * Creates a directory path with the trailing slash.
	 *
	 * @param string $path Directory path
	 * @return string
	 */
	protected function getDirPath(string $path): string
	{
		return '/' . trim($path, '/') . '/';
	}

	/**
	 * Fetches properties from the response.
	 *
	 * @param Response $response Response
	 * @return array
	 */
	protected function getProperties(Response $response): array
	{
		// Process the XML with properties
		$properties = [];
		$reader = new XmlReader();
		$reader->XML((string) $response->getBody());

		// Ignore warnings
		while (@$reader->read()) {
			if (($reader->nodeType === XMLReader::ELEMENT) && ($reader->name === 'D:prop')) {
				while (@$reader->read()) {
					// Element must not be empty and has to look something like <lp1:getcontentlength>13744</lp1:getcontentlength>
					if (($reader->nodeType === XMLReader::ELEMENT) && (!$reader->isEmptyElement)) {
						if (preg_match('~^lp\\d+:(.+)$~', $reader->name, $matches)) {
							// Apache
							$properties[$matches[1]] = $reader->getTextValue();
						} elseif (preg_match('~^D:(.+)$~', $reader->name, $matches)) {
							// Lighttpd
							$properties[$matches[1]] = $reader->getTextValue();
						}
					} elseif (($reader->nodeType === XMLReader::END_ELEMENT) && ($reader->name === 'D:prop')) {
						break;
					}
				}
			}
		}

		return $properties;
	}

	protected function getRandomServer(): string
	{
		return $this->servers[array_rand($this->servers)];
	}

	private function getStaticRequestOptions(): array
	{
		return [
			RequestOptions::HTTP_ERRORS => false,
			RequestOptions::EXPECT => false,
		];
	}

}
