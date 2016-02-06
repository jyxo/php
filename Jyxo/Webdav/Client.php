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

/**
 * Client for work with WebDav. Uses the http PHP extension.
 *
 * @category Jyxo
 * @package Jyxo\Webdav
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Client
{
	/** @var string */
	const METHOD_HEAD = 'HEAD';

	/** @var string */
	const METHOD_GET = 'GET';

	/** @var string */
	const METHOD_PUT = 'PUT';

	/** @var string */
	const METHOD_DELETE = 'DELETE';

	/** @var string */
	const METHOD_COPY = 'COPY';

	/** @var string */
	const METHOD_MOVE = 'MOVE';

	/** @var string */
	const METHOD_MKCOL = 'MKCOL';

	/** @var string */
	const METHOD_PROPFIND = 'PROPFIND';

	/** @var integer */
	const STATUS_200_OK = 200;

	/** @var integer */
	const STATUS_201_CREATED = 201;

	/** @var integer */
	const STATUS_204_NO_CONTENT = 204;

	/** @var integer */
	const STATUS_207_MULTI_STATUS = 207;

	/** @var integer */
	const STATUS_403_FORBIDDEN = 403;

	/** @var integer */
	const STATUS_404_NOT_FOUND = 404;

	/** @var integer */
	const STATUS_405_METHOD_NOT_ALLOWED = 405;

	/** @var integer */
	const STATUS_409_CONFLICT = 409;

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
		\GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 1,
		\GuzzleHttp\RequestOptions::TIMEOUT => 30
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
	 * @var boolean
	 */
	protected $parallelSending = true;

	/**
	 * If directories should be created automatically.
	 *
	 * If disabled, commands will throw an error if target directory doesn't exist.
	 *
	 * @var boolean
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
	 * Sets a request option.
	 *
	 * @param string $name Option name
	 * @param mixed $value Option value
	 *
	 * @see \GuzzleHttp\RequestOptions
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
	 * @return \Jyxo\Webdav\Client
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;
		return $this;
	}

	/**
	 * Enables/disables parallel request sending.
	 *
	 * @param boolean $parallelSending
	 * @return \Jyxo\Webdav\Client
	 */
	public function setParallelSending(bool $parallelSending): self
	{
		$this->parallelSending = $parallelSending;
		return $this;
	}

	/**
	 * Enables/disables automatic creation of target directories.
	 *
	 * @param boolean $createDirectoriesAutomatically
	 * @return \Jyxo\Webdav\Client
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
	 * @return boolean
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function exists(string $path): bool
	{
		$response = $this->sendRequest($this->getFilePath($path), self::METHOD_HEAD);
		return self::STATUS_200_OK === $response->getStatusCode();
	}

	/**
	 * Returns file contents.
	 *
	 * @param string $path File path
	 * @return string
	 * @throws \Jyxo\Webdav\FileNotExistException If the file does not exist
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function get(string $path): string
	{
		// Asking random server
		$path = $this->getFilePath($path);
		$response = $this->sendRequest($path, self::METHOD_GET);

		if (self::STATUS_200_OK !== $response->getStatusCode()) {
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
	 * @throws \Jyxo\Webdav\FileNotExistException If the file does not exist
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function getProperty(string $path, string $property = null)
	{
		// Asking random server
		$path = $this->getFilePath($path);
		$response = $this->sendRequest($path, self::METHOD_PROPFIND, ['Depth' => '0']);

		if (self::STATUS_207_MULTI_STATUS !== $response->getStatusCode()) {
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
	 * @throws \Jyxo\Webdav\FileNotCreatedException If the file cannot be created
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function put(string $path, string $data)
	{
		$this->processPut($this->getFilePath($path), $data, false);
	}

	/**
	 * Saves file contents to a remote file.
	 *
	 * @param string $path File path
	 * @param string $file Local file path
	 * @throws \Jyxo\Webdav\FileNotCreatedException If the file cannot be created
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function putFile(string $path, string $file)
	{
		$this->processPut($this->getFilePath($path), $file, true);
	}

	/**
	 * Copies a file.
	 * Does not work on Lighttpd.
	 *
	 * @param string $pathFrom Source file path
	 * @param string $pathTo Target file path
	 * @throws \Jyxo\Webdav\FileNotCopiedException If the file cannot be copied
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function copy(string $pathFrom, string $pathTo)
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
				'Destination' => $server . $pathTo
			]);
		}

		foreach ($this->sendAllRequests($requests) as $response) {
			// 201 means copied
			if (self::STATUS_201_CREATED !== $response->getStatusCode()) {
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
	 * @throws \Jyxo\Webdav\FileNotRenamedException If the file cannot be renamed
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function rename(string $pathFrom, string $pathTo)
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
				'Destination' => $server . $pathTo
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
	 * @throws \Jyxo\Webdav\FileNotDeletedException If the file cannot be deleted
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function unlink(string $path)
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
	 * @return boolean
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function isDir(string $dir): bool
	{
		// Asking random server
		$response = $this->sendRequest($this->getDirPath($dir), self::METHOD_PROPFIND, ['Depth' => '0']);

		// The directory does not exist or server does not support PROPFIND method
		if (self::STATUS_207_MULTI_STATUS !== $response->getStatusCode()) {
			return false;
		}

		// Fetches properties from the server
		$properties = $this->getProperties($response);

		// Checks if it is a directory
		return isset($properties['getcontenttype']) && ('httpd/unix-directory' === $properties['getcontenttype']);
	}

	/**
	 * Creates a directory.
	 *
	 * @param string $dir Directory path
	 * @param boolean $recursive Create directories recursively?
	 * @throws \Jyxo\Webdav\DirectoryNotCreatedException If the directory cannot be created
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function mkdir(string $dir, bool $recursive = true)
	{
		// If creating directories recursively, create the parent directory first
		$dir = trim($dir, '/');
		if ($recursive) {
			$dirs = explode('/', $dir);
		} else {
			$dirs = [$dir];
		}

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
	 * @throws \Jyxo\Webdav\DirectoryNotDeletedException If the directory cannot be deleted
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function rmdir(string $dir)
	{
		foreach ($this->sendAllRequests($this->createAllRequests($this->getDirPath($dir), self::METHOD_DELETE)) as $response) {
			// 204 means deleted
			if (self::STATUS_204_NO_CONTENT !== $response->getStatusCode()) {
				throw new DirectoryNotDeletedException(sprintf('Directory %s cannot be deleted.', $dir));
			}
		}
	}

	/**
	 * Processes a PUT request.
	 *
	 * @param string $path File path
	 * @param string $data Data
	 * @param boolean $isFile Determines if $data is a file name or actual data
	 * @throws \Jyxo\Webdav\DirectoryNotCreatedException If the target directory cannot be created
	 * @throws \Jyxo\Webdav\FileNotCreatedException If the file cannot be created
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	protected function processPut(string $path, string $data, bool $isFile)
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
					throw new \Jyxo\Webdav\FileNotCreatedException(sprintf('File %s cannot be created.', $path));
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
				throw new \Jyxo\Webdav\FileNotCreatedException(sprintf('File %s cannot be created.', $path), 0, $e);
			}
		}

		// Try again
		foreach ($this->sendAllRequests($requests) as $response) {
			// 201 means saved
			if (self::STATUS_201_CREATED !== $response->getStatusCode()) {
				throw new \Jyxo\Webdav\FileNotCreatedException(sprintf('File %s cannot be created.', $path));
			}
		}
	}

	/**
	 * Sends requests to all servers.
	 *
	 * @param \GuzzleHttp\Psr7\Request[] $requests Request list
	 * @return \GuzzleHttp\Psr7\Response[]
	 * @throws \Jyxo\Webdav\Exception On error
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
					$promises[$server] = $client->sendAsync($request, $this->requestOptions);
				}

				// Wait on all of the requests to complete
				$responses = [];
				foreach ($promises as $server => $promise) {
					$responses[$server] = $promise->wait();
				}

				// Log
				if (null !== $this->logger) {
					foreach ($responses as $server => $response) {
						$this->logger->log(sprintf("%s %d %s", $request->getMethod(), $response->getStatusCode(), $request->getUri()));
					}
				}

			} else {

				// Send by separate requests
				foreach ($requests as $server => $request) {
					$response = $client->send($request, $this->requestOptions);
					$responses[$server] = $response;

					// Log
					if (null !== $this->logger) {
						$this->logger->log(sprintf("%s %d %s", $request->getMethod(), $response->getStatusCode(), $request->getUri()));
					}
				}
			}

			return $responses;
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			throw new Exception($e->getMessage(), 0, $e);
		}
	}

	/**
	 * Sends a request.
	 *
	 * @param string $path Request path
	 * @param string $method Request method
	 * @param array $headers Array of headers
	 * @return \GuzzleHttp\Psr7\Response
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	protected function sendRequest(string $path, string $method, array $headers = []): \GuzzleHttp\Psr7\Response
	{
		try {
			// Send request to a random server
			$request = $this->createRequest($this->getRandomServer(), $path, $method, $headers);
			$response = $this->createClient()->send($request, $this->requestOptions);

			if (null !== $this->logger) {
				$this->logger->log(sprintf("%s %d %s", $request->getMethod(), $response->getStatusCode(), $request->getUri()));
			}

			return $response;
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
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
	 * @return \GuzzleHttp\Psr7\Request[]
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
	 * @return \GuzzleHttp\Psr7\Request
	 */
	protected function createRequest(string $server, string $path, string $method, array $headers = [], $body = null): \GuzzleHttp\Psr7\Request
	{
		return new \GuzzleHttp\Psr7\Request(
			$method,
			rtrim($server, '/') . $path,
			$headers,
			$body
		);
	}

	/**
	 * @return \GuzzleHttp\Client
	 */
	protected function createClient(): \GuzzleHttp\Client
	{
		return new \GuzzleHttp\Client([
			\GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => false,
			\GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
			\GuzzleHttp\RequestOptions::VERIFY => true,
			\GuzzleHttp\RequestOptions::DECODE_CONTENT => true,
			\GuzzleHttp\RequestOptions::EXPECT => false,
		]);
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
	 * @param \GuzzleHttp\Psr7\Response $response Response
	 * @return array
	 */
	protected function getProperties(\GuzzleHttp\Psr7\Response $response): array
	{
		// Process the XML with properties
		$properties = [];
		$reader = new \Jyxo\XmlReader();
		$reader->XML((string) $response->getBody());

		// Ignore warnings
		while (@$reader->read()) {
			if ((\XMLReader::ELEMENT === $reader->nodeType) && ('D:prop' === $reader->name)) {
				while (@$reader->read()) {
					// Element must not be empty and has to look something like <lp1:getcontentlength>13744</lp1:getcontentlength>
					if ((\XMLReader::ELEMENT === $reader->nodeType) && (!$reader->isEmptyElement)) {
						if (preg_match('~^lp\\d+:(.+)$~', $reader->name, $matches)) {
							// Apache
							$properties[$matches[1]] = $reader->getTextValue();
						} elseif (preg_match('~^D:(.+)$~', $reader->name, $matches)) {
							// Lighttpd
							$properties[$matches[1]] = $reader->getTextValue();
						}
					} elseif ((\XMLReader::END_ELEMENT === $reader->nodeType) && ('D:prop' === $reader->name)) {
						break;
					}
				}
			}
		}

		return $properties;
	}

	/**
	 * @return string
	 */
	protected function getRandomServer(): string
	{
		return $this->servers[array_rand($this->servers)];
	}
}
