<?php

/**
 * Jyxo Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/jyxo/php/blob/master/license.txt
 */

namespace Jyxo\Shell;

/**
 * Class for executing external commands.
 *
 * @category Jyxo
 * @package Jyxo\Shell
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Ondřej Procházka <libs@jyxo.com>
 * @author Matěj Humpál <libs@jyxo.com>
 */
class Client
{
	/**
	 * List of running processes.
	 *
	 * @var array
	 */
	protected $processList;

	/**
	 * Actual working directory.
	 *
	 * @var string
	 */
	protected $cwd;

	/**
	 * Environment properties.
	 *
	 * @var array
	 */
	protected $env = array();

	/**
	 * Stdout output.
	 *
	 * @var string
	 */
	protected $out;

	/**
	 * Stderr output.
	 *
	 * @var string
	 */
	protected $error;

	/**
	 * Constructor.
	 *
	 * @param string $cwd Working directory
	 * @param array $env Array of environment properties
	 */
	public function __construct($cwd = '', array $env = array())
	{
		$this->setCwd($cwd);
		$this->env = $_ENV;
		$this->setEnv($env);
	}

	/**
	 * Returns a list of processes.
	 *
	 * Works only on Linux.
	 *
	 * @return \Jyxo\Shell\Client
	 */
	public function loadProcessList()
	{
		$output = shell_exec('ps aux');

		$data = explode("\n", $output);

		foreach ($data as $value) {
			$value = preg_replace('/ +/', ' ', $value);
			$list = explode(' ', $value);
			$commands[$list[10]][] = $list[1];
		}

		$this->processList = $commands;

		return $this;
	}

	/**
	 * Checks if there is a process of the given name.
	 *
	 * Works only on Linux.
	 *
	 * @param string $name Process name
	 * @return boolean
	 */
	public function processExists($name)
	{
		return array_key_exists((string) $name, $this->processList);
	}

	/**
	 * Kills all processes of the given name.
	 *
	 * Works only on Linux.
	 *
	 * @param string $name Process name
	 * @return \Jyxo\Shell\Client
	 */
	public function killProcess($name)
	{
		shell_exec('killall -s KILL ' . (string) $name);

		return $this;
	}

	/**
	 * Sets working directory.
	 *
	 * Defaults to null.
	 *
	 * @param string $cwd Working directory
	 * @return \Jyxo\Shell\Client
	 */
	public function setCwd($cwd = '')
	{
		$this->cwd = (string) $cwd;

		return $this;
	}

	/**
	 * Adds one or more environment properties.
	 *
	 * @param array $env Array of properties
	 * @return \Jyxo\Shell\Client
	 */
	public function setEnv(array $env)
	{
		$this->env = array_merge($this->env, $env);

		return $this;
	}

	/**
	 * Removes environment properties.
	 *
	 * @return \Jyxo\Shell\Client
	 */
	public function clearEnv()
	{
		$this->env = $_ENV;

		return $this;
	}

	/**
	 * Executes an external command.
	 *
	 * Captures stdout and stderr.
	 * Throws an exception on status code != 0.
	 *
	 * @param string $cmd Command to execute
	 * @param int $status Status code
	 * @return \Jyxo\Shell\Client
	 * @throws \Jyxo\Shell\Exception On execution error
	 */
	public function exec($cmd, &$status = null)
	{
		static $descriptorSpec = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w')
		);

		$env = $this->env;
		if (ini_get('safe_mode')) {
			// If the safe_mode is set on, we have to check which properties we are allowed to set.

			$allowedPrefixes = explode(',', ini_get('safe_mode_allowed_env_vars'));
			$protectedVars = explode(',', ini_get('safe_mode_protected_env_vars'));

			// Throw away protected properties.
			$env = array_diff_key($env, array_fill_keys($protectedVars, true));

			// Throw away properties that do not have the allowed prefix.
			foreach ($env as $name => $value) {
				foreach ($allowedPrefixes as $prefix) {
					// Empty prefix - allow all properties.
					if ($prefix === '') {
						break 2;
					}

					if (substr($name, 0, strlen($prefix)) == $prefix) {
						continue 2;
					}
				}

				unset($env[$name]);
			}
		}

		$cmd = (string) $cmd;
		$process = proc_open($cmd, $descriptorSpec, $pipes, !empty($this->cwd) ? $this->cwd : null, !empty($env) ? $env : null);

		if (!is_resource($process)) {
			throw new \Jyxo\Shell\Exception('Unable to start shell process.');
		}

		$this->out = stream_get_contents($pipes[1]);
		fclose($pipes[1]);

		$this->error = stream_get_contents($pipes[2]);
		fclose($pipes[2]);

		$status = proc_close($process);

		if ($status !== 0) {
			throw new \Jyxo\Shell\Exception(
				'Command ' . $cmd . ' returned code ' . $status
					. '. Output: ' . $this->error
			);
		}

		return $this;
	}

	/**
	 * Returns stdout contents.
	 *
	 * @return string
	 */
	public function getOut()
	{
		return $this->out;
	}

	/**
	 * Returns stderr contents.
	 *
	 * @return string
	 */
	public function getError()
	{
		return $this->error;
	}
}
