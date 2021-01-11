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

namespace Jyxo\Shell;

use function array_diff_key;
use function array_fill_keys;
use function array_key_exists;
use function array_merge;
use function explode;
use function fclose;
use function ini_get;
use function is_resource;
use function preg_replace;
use function proc_close;
use function proc_open;
use function shell_exec;
use function stream_get_contents;
use function strlen;
use function substr;

/**
 * Class for executing external commands.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Ondřej Procházka
 * @author Matěj Humpál
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
	protected $env = [];

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
	public function __construct(string $cwd = '', array $env = [])
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
	 * @return Client
	 */
	public function loadProcessList(): self
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
	 * @return bool
	 */
	public function processExists(string $name): bool
	{
		return array_key_exists($name, $this->processList);
	}

	/**
	 * Kills all processes of the given name.
	 *
	 * Works only on Linux.
	 *
	 * @param string $name Process name
	 * @return Client
	 */
	public function killProcess(string $name): self
	{
		shell_exec('killall -s KILL ' . $name);

		return $this;
	}

	/**
	 * Sets working directory.
	 *
	 * Defaults to null.
	 *
	 * @param string $cwd Working directory
	 * @return Client
	 */
	public function setCwd(string $cwd = ''): Client
	{
		$this->cwd = $cwd;

		return $this;
	}

	/**
	 * Adds one or more environment properties.
	 *
	 * @param array $env Array of properties
	 * @return Client
	 */
	public function setEnv(array $env): Client
	{
		$this->env = array_merge($this->env, $env);

		return $this;
	}

	/**
	 * Removes environment properties.
	 *
	 * @return Client
	 */
	public function clearEnv(): Client
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
	 * @return Client
	 */
	public function exec(string $cmd, ?int &$status = null): Client
	{
		static $descriptorSpec = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];

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

					if (substr($name, 0, strlen($prefix)) === $prefix) {
						continue 2;
					}
				}

				unset($env[$name]);
			}
		}

		$cmd = (string) $cmd;
		$process = proc_open($cmd, $descriptorSpec, $pipes, !empty($this->cwd) ? $this->cwd : null, !empty($env) ? $env : null);

		if (!is_resource($process)) {
			throw new Exception('Unable to start shell process.');
		}

		$this->out = stream_get_contents($pipes[1]);
		fclose($pipes[1]);

		$this->error = stream_get_contents($pipes[2]);
		fclose($pipes[2]);

		$status = proc_close($process);

		if ($status !== 0) {
			throw new Exception('Command ' . $cmd . ' returned code ' . $status . '. Output: ' . $this->error);
		}

		return $this;
	}

	/**
	 * Returns stdout contents.
	 *
	 * @return string
	 */
	public function getOut(): string
	{
		return $this->out;
	}

	/**
	 * Returns stderr contents.
	 *
	 * @return string
	 */
	public function getError(): string
	{
		return $this->error;
	}

}
