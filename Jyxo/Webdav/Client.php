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
	/**
	 * Servers list.
	 *
	 * @var array
	 */
	private $servers = array();

	/**
	 * Connection options.
	 *
	 * @var array
	 */
	private $options = array(
		'connecttimeout' => 1,
		'timeout' => 30
	);

	/**
	 * File for log.
	 *
	 * @var string
	 */
	private $logFile = null;

	/**
	 * If pool is enabled.
	 *
	 * @var boolean
	 */
	private $poolEnabled = true;

	/**
	 * Request pool.
	 *
	 * @var \HttpRequestPool
	 */
	private $pool = null;

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
	 * Returns the HTTP request pool.
	 *
	 * @return \HttpRequestPool
	 */
	protected function getHttpRequestPool()
	{
		if (null === $this->pool) {
			$this->pool = new \HttpRequestPool();
		}

		return $this->pool;
	}

	/**
	 * Sets an option.
	 *
	 * @param string $name Option name
	 * @param mixed $value Option value
	 */
	public function setOption($name, $value)
	{
		$this->options[(string) $name] = $value;
	}

	/**
	 * Sets a log file.
	 *
	 * @param string $file
	 * @return \Jyxo\Webdav\Client
	 */
	public function setLogFile($file)
	{
		if ((is_file($file) && !is_writable($file)) || !is_writable(dirname($file))) {
			throw new \InvalidArgumentException('The log file is not writeable');
		}

		$this->logFile = (string) $file;

		return $this;
	}

	/**
	 * Enables/disables the HTTP request pool.
	 *
	 * @param boolean $enabled
	 * @return \Jyxo\Webdav\Client
	 */
	public function setPoolEnabled($enabled)
	{
		$this->poolEnabled = (bool) $enabled;
		return $this;
	}

	/**
	 * Checks if a file exists.
	 *
	 * @param string $path File path
	 * @return boolean
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function exists($path)
	{
		$response = $this->sendRequest($this->getFilePath($path), \HttpRequest::METH_HEAD);
		return (200 === $response->getResponseCode());
	}

	/**
	 * Returns file contents.
	 *
	 * @param string $path File path
	 * @return string
	 * @throws \Jyxo\Webdav\FileNotExistException If the file does not exist
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function get($path)
	{
		// Asking random server
		$path = $this->getFilePath($path);
		$response = $this->sendRequest($path, \HttpRequest::METH_GET);

		if (200 !== $response->getResponseCode()) {
			throw new FileNotExistException(sprintf('File %s does not exist.', $path));
		}

		return $response->getBody();
	}

	/**
	 * Returns a file property.
	 * If no particular property is set, all properties are returned.
	 *
	 * @param string $path File path
	 * @param string $property Property name
	 * @return mixed
	 * @throws \Jyxo\Webdav\FileNotExistException If the file does not exist
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function getProperty($path, $property = '')
	{
		// Asking random server
		$path = $this->getFilePath($path);
		$response = $this->sendRequest($path, \HttpRequest::METH_PROPFIND, array('Depth' => '0'));

		if (207 !== $response->getResponseCode()) {
			throw new FileNotExistException(sprintf('File %s does not exist.', $path));
		}

		// Fetches file properties from the server
		$properties = $this->getProperties($response);

		// Returns the requested property value
		if (isset($properties[$property])) {
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
	 * @return boolean
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function put($path, $data)
	{
		return $this->processPut($this->getFilePath($path), $data, false);
	}

	/**
	 * Saves file contents to a remote file.
	 *
	 * @param string $path File path
	 * @param string $file Local file path
	 * @return boolean
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function putFile($path, $file)
	{
		return $this->processPut($this->getFilePath($path), $file, true);
	}

	/**
	 * Copies a file.
	 * Does not work on Lighttpd.
	 *
	 * @param string $pathFrom Source file path
	 * @param string $pathTo Target file path
	 * @return boolean
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function copy($pathFrom, $pathTo)
	{
		$requestList = $this->getRequestList($this->getFilePath($pathFrom), \HttpRequest::METH_COPY);
		foreach ($requestList as $server => $request) {
			$request->addHeaders(array('Destination' => $server . $this->getFilePath($pathTo)));
		}

		foreach ($this->sendPool($requestList) as $request) {
			// 201 means copied
			if (201 !== $request->getResponseCode()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Renames a file.
	 * Does not work on Lighttpd.
	 *
	 * @param string $pathFrom Original file name
	 * @param string $pathTo New file name
	 * @return boolean
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function rename($pathFrom, $pathTo)
	{
		$requestList = $this->getRequestList($this->getFilePath($pathFrom), \HttpRequest::METH_MOVE);
		foreach ($requestList as $server => $request) {
			$request->addHeaders(array('Destination' => $server . $this->getFilePath($pathTo)));
		}

		foreach ($this->sendPool($requestList) as $request) {
			// 201 means renamed
			if (201 !== $request->getResponseCode()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Deletes a file.
	 * Contains a check preventing from deleting directories.
	 *
	 * @param string $path Directory path
	 * @return boolean
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function unlink($path)
	{
		// We do not delete directories
		if ($this->isDir($path)) {
			return false;
		}

		foreach ($this->send($this->getFilePath($path), \HttpRequest::METH_DELETE) as $request) {
			// 204 means deleted
			if (204 !== $request->getResponseCode()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks if a directory exists.
	 *
	 * @param string $dir Directory path
	 * @return boolean
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function isDir($dir)
	{
		// Asking random server
		$response = $this->sendRequest($this->getDirPath($dir), \HttpRequest::METH_PROPFIND, array('Depth' => '0'));

		// The directory does not exist
		if (207 !== $response->getResponseCode()) {
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
	 * @return boolean
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function mkdir($dir, $recursive = true)
	{
		// If creating directories recursively, create the parent directory first
		$dir = trim($dir, '/');
		if ($recursive) {
			$dirs = explode('/', $dir);
		} else {
			$dirs = array($dir);
		}

		$path = '';
		foreach ($dirs as $dir) {
			$path .= rtrim($dir);
			$path = $this->getDirPath($path);

			foreach ($this->send($path, \HttpRequest::METH_MKCOL) as $request) {
				switch ($request->getResponseCode()) {
					// The directory was created
					case 201:
						break;
					// The directory already exists
					case 405:
						break;
					// The directory could not be created
					default:
						return false;
				}
			}
		}

		// The directory was created
		return true;
	}

	/**
	 * Deletes a directory.
	 *
	 * @param string $dir Directory path
	 * @return boolean
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function rmdir($dir)
	{
		foreach ($this->send($this->getDirPath($dir), \HttpRequest::METH_DELETE) as $request) {
			// 204 means deleted
			if (204 !== $request->getResponseCode()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Processes a PUT request.
	 *
	 * @param string $path File path
	 * @param string $data Data
	 * @param boolean $isFile Determines if $data is a file name or actual data
	 * @return boolean
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	private function processPut($path, $data, $isFile)
	{
		$success = true;
		foreach ($this->sendPut($path, $data, $isFile) as $request) {
			switch ($request->getResponseCode()) {
				// Saved
				case 200:
				case 201:
					break;
				// An existing file was modified
				case 204:
					break;
				// The directory might not exist
				case 403:
				case 404:
				case 409:
					$success = false;
					break;
				// Could not save
				default:
					return false;
			}
		}

		// Saved
		if ($success) {
			return true;
		}

		// Not saved, try creating the directory first
		if (!$this->mkdir(dirname($path))) {
			return false;
		}

		// Try again
		foreach ($this->sendPut($path, $data, $isFile) as $request) {
			// 201 means saved
			if (201 !== $request->getResponseCode()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Sends a PUT request.
	 *
	 * @param string $path File path
	 * @param string $data Data
	 * @param boolean $isFile Determines if $data is a file name or actual data
	 * @return \ArrayObject
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	private function sendPut($path, $data, $isFile)
	{
		$requestList = $this->getRequestList($path, \HttpRequest::METH_PUT);
		foreach ($requestList as $request) {
			if ($isFile) {
				$request->setPutFile($data);
			} else {
				$request->setPutData($data);
			}
		}

		$this->sendPool($requestList);

		return $requestList;
	}

	/**
	 * Creates a request pool and sends it.
	 *
	 * @param string $path Request path
	 * @param integer $method Request method
	 * @param array $headers Array of headers
	 * @return \ArrayObject
	 */
	private function send($path, $method, array $headers = array())
	{
		$requestList = $this->getRequestList($path, $method, $headers);
		$this->sendPool($requestList);
		return $requestList;
	}

	/**
	 * Sends a request pool.
	 *
	 * @param \ArrayObject $requestList Request list
	 * @return \ArrayObject
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	private function sendPool(\ArrayObject $requestList)
	{
		try {
			if ($this->poolEnabled) {
				// Send by pool

				// Clean the pool
				$this->getHttpRequestPool()->reset();

				// Attach requests
				foreach ($requestList as $request) {
					$this->getHttpRequestPool()->attach($request);
				}

				// Send
				$this->getHttpRequestPool()->send();
			} else {
				// Send by separate requests
				foreach ($requestList as $request) {
					$request->send();
				}
			}

			// Log
			if ($this->logFile) {
				$this->log($requestList);
			}

			return $requestList;
		} catch (\HttpException $e) {
			// Find the innermost exception
			$inner = $e;
			while (null !== $inner->innerException) {
				$inner = $inner->innerException;
			}
			throw new Exception($inner->getMessage(), 0, $inner);
		}
	}

	/**
	 * Logs the result of a HTTP call.
	 *
	 * @param \ArrayObject $requestList Request list
	 */
	protected function log(\ArrayObject $requestList)
	{
		$datetime = date('Y-m-d H:i:s');
		foreach ($requestList as $request) {
			$url = preg_replace('~://[^@]+@~', '://***@', $request->getUrl());
			$data = sprintf("[%s]: %s %d %s\n", $datetime, $this->getMethodName($request->getMethod()), $request->getResponseCode(), $url);
			file_put_contents($this->logFile, $data, FILE_APPEND);
		}
	}

	/**
	 * Sends a request.
	 *
	 * @param string $path Request path
	 * @param integer $method Request method
	 * @param array $headers Array of headers
	 * @return \HttpMessage
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	private function sendRequest($path, $method, array $headers = array())
	{
		try {
			// Send request to a random server
			$request = $this->getRequest($this->servers[array_rand($this->servers)] . $path, $method, $headers);
			return $request->send();
		} catch (\HttpException $e) {
			throw new Exception($e->getMessage(), 0, $e);
		}
	}

	/**
	 * Returns a list of requests; one for each server.
	 *
	 * @param string $path Request path
	 * @param integer $method Request method
	 * @param array $headers Array of headers
	 * @return \ArrayObject
	 */
	private function getRequestList($path, $method, array $headers = array())
	{
		$requestList = new \ArrayObject();
		foreach ($this->servers as $server) {
			$requestList->offsetSet($server, $this->getRequest($server . $path, $method, $headers));
		}
		return $requestList;
	}

	/**
	 * Creates a request.
	 *
	 * @param string $url Request URL
	 * @param integer $method Request method
	 * @param array $headers Array of headers
	 * @return \HttpRequest
	 */
	private function getRequest($url, $method, array $headers = array())
	{
		$request = new \HttpRequest($url, $method, $this->options);
		$request->setHeaders(array('Expect' => ''));
		$request->addHeaders($headers);
		return $request;
	}

	/**
	 * Creates a file path without the trailing slash.
	 *
	 * @param string $path File path
	 * @return string
	 */
	private function getFilePath($path)
	{
		return '/' . trim($path, '/');
	}

	/**
	 * Creates a directory path with the trailing slash.
	 *
	 * @param string $path Directory path
	 * @return string
	 */
	private function getDirPath($path)
	{
		return '/' . trim($path, '/') . '/';
	}

	/**
	 * Fetches properties from the response.
	 *
	 * @param \HttpMessage $response Response
	 * @return array
	 */
	private function getProperties(\HttpMessage $response)
	{
		// Process the XML with properties
		$properties = array();
		$reader = new \Jyxo\XmlReader();
		$reader->XML($response->getBody());

		// Ignore warnings
		while (@$reader->read()) {
			if ((\XMLReader::ELEMENT === $reader->nodeType) && ('D:prop' === $reader->name)) {
				while (@$reader->read()) {
					// Element must not be empty and has to look something like <lp1:getcontentlength>13744</lp1:getcontentlength>
					if ((\XMLReader::ELEMENT === $reader->nodeType) && (!$reader->isEmptyElement)) {
						if (preg_match('~^lp\d+:(.+)$~', $reader->name, $matches)) {
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
	 * Returns HTTP method name.
	 *
	 * @param integer $method
	 * @return string
	 */
	private function getMethodName($method)
	{
		static $methods = array();

		if (empty($methods)) {
			$reflection = new \ReflectionClass('\\HttpRequest');
			foreach ($reflection->getConstants() as $methodName => $methodId) {
				if (0 === strpos($methodName, 'METH_')) {
					$methods[$methodId] = substr($methodName, 5);
				}
			}
		}

		return $methods[$method];
	}
}
