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

namespace Jyxo\Svn;

use Throwable;
use function array_merge;
use function escapeshellarg;
use function escapeshellcmd;
use function is_numeric;
use function sprintf;

/**
 * SVN client for PHP.
 *
 * Does not use the php_svn extension, but executes SVN binaries directly.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Matěj Humpál
 * @author Ondřej Nešpor
 */
class Client
{

	/**
	 * SVN username.
	 *
	 * @var string
	 */
	protected $user = '';

	/**
	 * SVN user password.
	 *
	 * @var string
	 */
	protected $password = '';

	/**
	 * Additional SVN parameters.
	 *
	 * @var array
	 */
	protected $additional = [];

	/**
	 * Path to the SVN binary.
	 *
	 * @var string
	 */
	protected $svnBinary = '/usr/bin/svn';

	/**
	 * Constructor.
	 *
	 * @param string $user SVN username
	 * @param string $password SVN user password
	 * @param array $additional Additional parameters
	 * @param string $svnBinary SVN binary path
	 */
	public function __construct(string $user = '', string $password = '', array $additional = [], string $svnBinary = '')
	{
		$this->user = $user;
		$this->password = $password;
		$this->additional = $additional;
		$this->svnBinary = $svnBinary;
	}

	/**
	 * SVN checkout.
	 *
	 * @param string $url Repository URL
	 * @param string $path Local working copy path
	 * @param mixed $params Additional parameters
	 * @param string $user SVN username
	 * @param string $password SVN user password
	 * @return Result
	 */
	public function checkout(string $url, string $path, $params = null, string $user = '', string $password = ''): Result
	{
		return $this->callSvn('checkout', $user, $password, array_merge((array) $url, (array) $params, (array) $path));
	}

	/**
	 * SVN checkout.
	 *
	 * @param string $url Repository URL
	 * @param string $path Local working copy path
	 * @param mixed $params Additional parameters
	 * @param string $user SVN username
	 * @param string $password SVN user password
	 * @return Result
	 */
	public function co(string $url, string $path, $params = null, string $user = '', string $password = ''): Result
	{
		return $this->checkout($url, $path, $params, $user, $password);
	}

	/**
	 * SVN Update.
	 *
	 * @param string $path Local working copy path
	 * @param mixed $params Additional parameters
	 * @param string $user SVN username
	 * @param string $password SVN user password
	 * @return Result
	 */
	public function update(string $path, $params = null, string $user = '', string $password = ''): Result
	{
		return $this->callSvn('update', $user, $password, array_merge((array) $params, (array) $path));
	}

	/**
	 * SVN Update.
	 *
	 * @param string $path Local working copy path
	 * @param mixed $params Additional parameters
	 * @param string $user SVN username
	 * @param string $password SVN user password
	 * @return Result
	 */
	public function up(string $path, $params = null, string $user = '', string $password = ''): Result
	{
		return $this->update($path, $params, $user, $password);
	}

	/**
	 * SVN commit.
	 *
	 * @param string $path Local working copy path
	 * @param string $message Commit message
	 * @param mixed $params Additional parameters
	 * @param string $user SVN username
	 * @param string $password SVN user password
	 * @return Result
	 */
	public function commit(string $path, string $message, $params = null, string $user = '', string $password = ''): Result
	{
		return $this->callSvn('commit', $user, $password, array_merge((array) $params, ['-m' => $message], (array) $path));
	}

	/**
	 * SVN commit.
	 *
	 * @param string $path Local working copy path
	 * @param string $message Commit message
	 * @param mixed $params Additional parameters
	 * @param string $user SVN username
	 * @param string $password SVN user password
	 * @return Result
	 */
	public function ci(string $path, string $message, $params = null, string $user = '', string $password = ''): Result
	{
		return $this->commit($path, $message, $params, $user, $password);
	}

	/**
	 * Runs SVN add on the given path.
	 *
	 * @param array $path Path to be added to SVN
	 * @return Result
	 */
	public function add(array $path): Result
	{
		return $this->callSvn('add', false, false, $path);
	}

	/**
	 * Runs SVN delete on the given path.
	 *
	 * @param array $path Path to be deleted from SVN
	 * @return Result
	 */
	public function delete(array $path): Result
	{
		return $this->callSvn('delete', false, false, $path);
	}

	/**
	 * Retrieves SVN status information of the given path.
	 *
	 * @param array $path Checked path
	 * @return Result
	 */
	public function status(array $path): Result
	{
		return $this->callSvn('status', false, false, $path);
	}

	/**
	 * Sets SVN username.
	 *
	 * @param string $user Username
	 * @return Client
	 */
	public function setUser(string $user): Client
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Sets SVN user password.
	 *
	 * @param string $password Password
	 * @return Client
	 */
	public function setPassword(string $password): Client
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * Sets additional parameters.
	 *
	 * @param array $params Array of parameters
	 * @return Client
	 */
	public function setAdditionalParams(array $params): Client
	{
		$this->additional = $params;

		return $this;
	}

	/**
	 * Sets SVN binary path.
	 *
	 * @param string $path Path to the SVN binary
	 * @return Client
	 */
	public function setSvnBinary(string $path): Client
	{
		$this->svnBinary = $path;

		return $this;
	}

	/**
	 * Adds an additional parameter.
	 *
	 * @param string $param Parameter name
	 * @param string $value Parameter value
	 * @return Client
	 */
	public function addAdditionalParam(string $param, string $value = ''): Client
	{
		if (!empty($value)) {
			$this->additional[$param] = escapeshellarg($value);
		} else {
			$this->additional[] = $param;
		}

		return $this;
	}

	/**
	 * Executes SVN binary with given parameters.
	 *
	 * @param string $action Action
	 * @param string $user Username
	 * @param string $password User password
	 * @param array $params Additional parameters
	 * @return Result
	 * @throws Exception On execute error
	 */
	protected function callSvn(string $action, string $user, string $password, array $params): Result
	{
		try {

			$command = escapeshellcmd($this->svnBinary) . ' ' . escapeshellarg($action);

			$command .= $this->getUserString($user);
			$command .= $this->getPasswordString($password);

			switch ($action) {
				case 'add':
				case 'delete':
					$command .= $this->getAdditionalParams($params, true);

					break;
				default:
					$command .= $this->getAdditionalParams($params, false);

					break;
			}

			try {

				$shell = new \Jyxo\Shell\Client();
				$shell->exec($command, $status);

				return new Result($action, $shell->getOut(), $status);
			} catch (\Jyxo\Shell\Exception $e) {
				throw $e;
			}
		} catch (Throwable $e) {
			throw new Exception(sprintf('SVN %s failed: %s', $action, $e->getMessage()), 0, $e);
		}
	}

	/**
	 * Returns SVN username with the given value for use as SVN binary parameter.
	 *
	 * Username given in the argument has precedence over the value stored in object's attribute.
	 * Returns empty string if no username is set in any way.
	 *
	 * @param mixed $user Username
	 * @return string
	 */
	protected function getUserString($user = ''): string
	{
		if ($user === false) {
			return '';
		}

		if (!empty($user)) {
			return ' --username ' . escapeshellarg($user);
		}

		if (!empty($this->user)) {
			return ' --username ' . escapeshellarg($this->user);
		}

		return '';
	}

	/**
	 * Returns SVN user password with the given value for use as SVN binary parameter.
	 *
	 * Password given in the argument has precedence over the value stored in object's attribute.
	 * Returns empty string if no password is set in any way.
	 *
	 * @param mixed $password Password
	 * @return string
	 */
	protected function getPasswordString($password = ''): string
	{
		if ($password === false) {
			return '';
		}

		if (!empty($password)) {
			return ' --password ' . escapeshellarg($password);
		}

		if (!empty($this->password)) {
			return ' --password ' . escapeshellarg($this->password);
		}

		return '';
	}

	/**
	 * Returns additional parameters with the given value for use as SVN binary parameters.
	 *
	 * Parameters given in the argument have precedence over values stored in object's attribute.
	 * If parameters are given as arrays, they get merged.
	 *
	 * Returns empty string if no parameters are set in any way.
	 *
	 * @param mixed[] $params Parameters
	 * @param bool $pathsOnly Use only path-parameters (not beginning with a dash "-")
	 * @return string
	 */
	protected function getAdditionalParams(array $params = [], bool $pathsOnly = false): string
	{
		$ret = ' ';

		foreach ($this->additional as $param => $value) {

			// If the key exists in $params or it is numeric, skip it.
			if (isset($params[$param]) && !is_numeric($param)) {
				continue;
			}

			// If we want only paths, skip parameters beginning with a dash "-".
			if ($pathsOnly && ($param[0] === '-' || $value[0] === '-')) {
				continue;
			}

			// If the key is not numeric, add it as well.
			if (!is_numeric($param)) {
				$ret .= ' ' . $param;
			}
			// And finally add the parameter value.
			$ret .= ' ' . $value;

		}

		foreach ((array) $params as $param => $value) {

			// If we want only paths.
			if ($pathsOnly && ($param[0] === '-' || $value[0] === '-')) {
				continue;
			}

			if (!is_numeric($param)) {
				$ret .= ' ' . $param;
			}
			$ret .= ' ' . escapeshellarg($value);

		}

		return $ret;
	}

}
