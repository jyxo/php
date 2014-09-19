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
	protected $servers = array();

	/**
	 * Connection options.
	 *
	 * @var array
	 */
	protected $options = array(
		'connecttimeout' => 1,
		'timeout' => 30
	);

	/**
	 * File for log.
	 *
	 * @var string
	 */
	protected $logFile = null;

	/**
	 * If pool is enabled.
	 *
	 * @var boolean
	 */
	protected $poolEnabled = true;

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
	 * Enables/disables automatic creation of target directories.
	 *
	 * @param boolean $createDirectoriesAutomatically
	 * @return \Jyxo\Webdav\Client
	 */
	public function setCreateDirectoriesAutomatically($createDirectoriesAutomatically)
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
	public function exists($path)
	{
		$response = $this->sendRequest($this->getFilePath($path), \HttpRequest::METH_HEAD);
		return self::STATUS_200_OK === $response->getResponseCode();
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

		if (self::STATUS_200_OK !== $response->getResponseCode()) {
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

		if (self::STATUS_207_MULTI_STATUS !== $response->getResponseCode()) {
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
	 * @throws \Jyxo\Webdav\FileNotCreatedException If the file cannot be created
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	public function put($path, $data)
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
	public function putFile($path, $file)
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
	public function copy($pathFrom, $pathTo)
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

		$requestList = $this->getRequestList($this->getFilePath($pathFrom), \HttpRequest::METH_COPY);
		foreach ($requestList as $server => $request) {
			$request->addHeaders(array('Destination' => $server . $pathTo));
		}

		foreach ($this->sendPool($requestList) as $request) {
			// 201 means copied
			if (self::STATUS_201_CREATED !== $request->getResponseCode()) {
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
	public function rename($pathFrom, $pathTo)
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

		$requestList = $this->getRequestList($this->getFilePath($pathFrom), \HttpRequest::METH_MOVE);
		foreach ($requestList as $server => $request) {
			$request->addHeaders(array('Destination' => $server . $pathTo));
		}

		foreach ($this->sendPool($requestList) as $request) {
			switch ($request->getResponseCode()) {
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
	public function unlink($path)
	{
		// We do not delete directories
		try {
			if ($this->isDir($path)) {
				throw new FileNotDeletedException(sprintf('The path %s is a directory.', $path));
			}
		} catch (\Jyxo\Webdav\Exception $e) {
			if (HTTP_E_INVALID_PARAM === $e->getPrevious()->getCode()) {
				// Probably no PROPFIND support
			} else {
				throw $e;
			}
		}

		foreach ($this->send($this->getFilePath($path), \HttpRequest::METH_DELETE) as $request) {
			// 204 means deleted
			if (self::STATUS_204_NO_CONTENT !== $request->getResponseCode()) {
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
	public function isDir($dir)
	{
		// Asking random server
		$response = $this->sendRequest($this->getDirPath($dir), \HttpRequest::METH_PROPFIND, array('Depth' => '0'));

		// The directory does not exist
		if (self::STATUS_207_MULTI_STATUS !== $response->getResponseCode()) {
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
	public function rmdir($dir)
	{
		foreach ($this->send($this->getDirPath($dir), \HttpRequest::METH_DELETE) as $request) {
			// 204 means deleted
			if (self::STATUS_204_NO_CONTENT !== $request->getResponseCode()) {
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
	protected function processPut($path, $data, $isFile)
	{
		$success = true;
		foreach ($this->sendPut($path, $data, $isFile) as $request) {
			switch ($request->getResponseCode()) {
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
		foreach ($this->sendPut($path, $data, $isFile) as $request) {
			// 201 means saved
			if (self::STATUS_201_CREATED !== $request->getResponseCode()) {
				throw new \Jyxo\Webdav\FileNotCreatedException(sprintf('File %s cannot be created.', $path));
			}
		}
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
	protected function sendPut($path, $data, $isFile)
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
	protected function send($path, $method, array $headers = array())
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
	protected function sendPool(\ArrayObject $requestList)
	{
		try {
			if ($this->poolEnabled) {
				// Send by pool

				// Create pool
				$pool = new \HttpRequestPool();

				// Attach requests
				foreach ($requestList as $request) {
					$pool->attach($request);
				}

				// Send
				$pool->send();
			} else {
				// Send by separate requests
				foreach ($requestList as $request) {
					$request->send();
				}
			}

			// Log
			if ($this->logFile) {
				$datetime = date('Y-m-d H:i:s');
				foreach ($requestList as $request) {
					$data = sprintf("[%s]: %s %d %s\n", $datetime, $this->getMethodName($request->getMethod()), $request->getResponseCode(), $request->getUrl());
					file_put_contents($this->logFile, $data, FILE_APPEND);
				}
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
	 * Sends a request.
	 *
	 * @param string $path Request path
	 * @param integer $method Request method
	 * @param array $headers Array of headers
	 * @return \HttpMessage
	 * @throws \Jyxo\Webdav\Exception On error
	 */
	protected function sendRequest($path, $method, array $headers = array())
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
	protected function getRequestList($path, $method, array $headers = array())
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
	protected function getRequest($url, $method, array $headers = array())
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
	protected function getFilePath($path)
	{
		return '/' . trim($path, '/');
	}

	/**
	 * Creates a directory path with the trailing slash.
	 *
	 * @param string $path Directory path
	 * @return string
	 */
	protected function getDirPath($path)
	{
		return '/' . trim($path, '/') . '/';
	}

	/**
	 * Fetches properties from the response.
	 *
	 * @param \HttpMessage $response Response
	 * @return array
	 */
	protected function getProperties(\HttpMessage $response)
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
	 * Returns HTTP method name.
	 *
	 * @param integer $method
	 * @return string
	 */
	protected function getMethodName($method)
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
