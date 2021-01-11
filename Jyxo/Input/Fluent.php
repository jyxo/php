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

namespace Jyxo\Input;

use BadMethodCallException;
use function array_key_exists;
use function array_merge;
use function reset;
use function uniqid;

/**
 * Class implementing "fluent" design pattern for \Jyxo\Input.
 *
 * Allows chaining multiple validators and checking multiple values in one validation cycle.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class Fluent
{

	/**
	 * Validator class names prefix.
	 */
	public const VALIDATORS_PREFIX = '\Jyxo\Input\Validator\\';

	/**
	 * Filter class names prefix.
	 */
	public const FILTERS_PREFIX = '\Jyxo\Input\Filter\\';

	/**
	 * All chains.
	 *
	 * @var array
	 */
	private $chains = [];

	/**
	 * All values.
	 *
	 * @var array
	 */
	private $values = [];

	/**
	 * Default variable values.
	 *
	 * @var array
	 */
	private $default = [];

	/**
	 * Current variable.
	 *
	 * @var string
	 */
	private $currentName;

	/**
	 * Current chain.
	 *
	 * @var Chain
	 */
	private $chain = null;

	/**
	 * Errors.
	 *
	 * @var array
	 */
	private $errors = [];

	/**
	 * \Jyxo\Input objects factory.
	 *
	 * @var Factory
	 */
	private $factory;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->factory = new Factory();
		$this->all();
	}

	/**
	 * Starts a new value checking.
	 *
	 * @param mixed $var Value to check.
	 * @param string $name Variable name
	 * @return Fluent
	 */
	public function check($var, string $name): self
	{
		$this->chain = new Chain();
		$this->chains[$name] = $this->chain;
		$this->values[$name] = $var;
		$this->default[$name] = null;
		$this->currentName = $name;

		return $this;
	}

	/**
	 * Validates all variables.
	 *
	 * @return Fluent
	 */
	public function all(): self
	{
		$this->chain = new Chain();
		$this->chains[uniqid('fluent:')] = $this->chain;
		$this->currentName = null;

		return $this;
	}

	/**
	 * Sets a default value in case the validation fails.
	 *
	 * @param mixed $value Default value
	 * @return Fluent
	 */
	public function defaultValue($value): self
	{
		if ($this->currentName === null) {
			throw new BadMethodCallException('No active variable');
		}

		$this->default[$this->currentName] = $value;

		return $this;
	}

	/**
	 * Adds a validator to the chain.
	 *
	 * @param string $name Validator name
	 * @param string $errorMessage Validator error message
	 * @param mixed $param Additional validator parameter
	 * @return Fluent
	 */
	public function validate(string $name, ?string $errorMessage = null, $param = null): self
	{
		$this->chain->addValidator($this->factory->getValidatorByName($name, $param), $errorMessage);

		return $this;
	}

	/**
	 * Adds a filter to the chain.s
	 *
	 * @param string $name Filter name
	 * @param mixed $param Additional filter parameter
	 * @return Fluent
	 */
	public function filter(string $name, $param = null): self
	{
		$this->chain->addFilter($this->factory->getFilterByName($name, $param));

		return $this;
	}

	/**
	 * Adds a subchain to the current chain that treats the value a an array.
	 * Automatically adds the isArray validator.
	 *
	 * @param bool $addFilter Add the Trim filter (removes empty elements)
	 * @return Fluent
	 */
	public function walk(bool $addFilter = true): self
	{
		$this->validate('isArray');

		if ($addFilter !== false) {
			$this->filter('trim');
		}

		$this->chain = $this->chain->addWalk();

		return $this;
	}

	/**
	 * Adds a conditional chain.
	 *
	 * If there are conditions in the current chain, adds the condition as a subchain.
	 *
	 * @param string $name Validator name
	 * @param mixed $param Additional validator parameter
	 * @return Fluent
	 */
	public function condition(string $name, $param = null): self
	{
		$condChain = new Chain\Conditional($this->factory->getValidatorByName($name, $param));

		if ($this->chain->isEmpty() === true) {
			// The actual chain is empty, can be replaced by the condition
			$this->chain = $condChain;

			if ($this->currentName === null) {
				throw new BadMethodCallException('No active variable');
			}

			$this->chains[$this->currentName] = $condChain;
		} else {
			$this->chain = $this->chain->addCondition($condChain);
		}

		return $this;
	}

	/**
	 * Closes a chain.
	 *
	 * @return Fluent
	 */
	public function close(): self
	{
		$this->chain = $this->chain->close();

		return $this;
	}

	/**
	 * Performs validation and filtering of all variables.
	 *
	 * @param bool $assocErrors Return error messages in an associative array
	 * @return bool
	 */
	public function isValid(bool $assocErrors = false): bool
	{
		$valid = true;

		foreach ($this->chains as $name => $chain) {
			/** @var Chain $chain */
			if (array_key_exists($name, $this->values)) {
				// Variable
				if (!$this->checkChain($chain, $this->values[$name], $this->default[$name], $assocErrors ? $name : null)) {
					$valid = false;
				}
			} elseif (!$chain->isEmpty()) {
				foreach ($this->values as $name => &$value) {
					if (!$this->checkChain($chain, $value, $this->default[$name])) {
						$valid = false;

						// No need to check other variables
						break;
					}
				}
			}
		}

		return $valid;
	}

	/**
	 * Calls isValid(), but throws an exception on error.
	 *
	 * The exception contains only the first validation error message.
	 */
	public function validateAll(): void
	{
		if (!$this->isValid()) {
			throw new Validator\Exception(reset($this->errors) ?: 'Validation failed');
		}
	}

	/**
	 * Returns all values.
	 *
	 * @return array
	 */
	public function getValues(): array
	{
		return $this->values;
	}

	/**
	 * Returns a value by name.
	 *
	 * @param string $name Variable name
	 * @return mixed
	 */
	public function getValue(string $name)
	{
		if (!array_key_exists($name, $this->values)) {
			throw new Exception('Value is not present');
		}

		return $this->values[$name];
	}

	/**
	 * Returns errors.
	 *
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * Checks a POST variable.
	 *
	 * @param string $name Variable name
	 * @param mixed $default Default value
	 * @return Fluent
	 */
	public function post(string $name, $default = null): self
	{
		$this->addToCheck($_POST, $name, $default);

		return $this;
	}

	/**
	 * Checks a GET variable.
	 *
	 * @param string $name Variable name
	 * @param mixed $default Default value
	 * @return Fluent
	 */
	public function query(string $name, $default = null): self
	{
		$this->addToCheck($_GET, $name, $default);

		return $this;
	}

	/**
	 * Checks a POST/GET variable
	 *
	 * @param string $name Variable name
	 * @param mixed $default Default value
	 * @return Fluent
	 */
	public function request(string $name, $default = null): self
	{
		$this->addToCheck($_REQUEST, $name, $default);

		return $this;
	}

	/**
	 * Checks file upload.
	 *
	 * Requires \Jyxo\Input\Upload.
	 *
	 * @see \Jyxo\Input\Upload
	 * @param string $index File index
	 * @return Fluent
	 */
	public function file(string $index): self
	{
		$validator = new Validator\Upload();
		$file = new Upload($index);
		$this
			->check($file, $index)
			->validate($validator)
			->filter($validator);

		return $this;
	}

	/**
	 * Checks a chain.
	 *
	 * @param Chain $chain Validation chain
	 * @param mixed $value Input value
	 * @param mixed $default Default value to be used in case the validation fails
	 * @param string $name Chain name to be used in the error array
	 * @return bool
	 */
	private function checkChain(Chain $chain, &$value, $default, ?string $name = null): bool
	{
		$valid = true;

		if ($chain->isValid($value)) {
			$value = $chain->getValue();
		} elseif ($default !== null) {
			$value = $default;
		} else {
			$valid = false;
			// If we have $name set, we want an associative array
			$errors = empty($name) ? $chain->getErrors() : [$name => $chain->getErrors()];
			$this->errors = array_merge($this->errors, $errors);
		}

		return $valid;
	}

	/**
	 * Adds a variable to the chain.
	 *
	 * @param array $global Variable array
	 * @param string $name Variable name
	 * @param mixed $default Default value
	 */
	private function addToCheck(array $global, string $name, $default = null): void
	{
		$var = $global[$name] ?? $default;
		$this->check($var, $name);
	}

	/**
	 * Magic getter for easier retrieving of values.
	 *
	 * @param string $offset Value name
	 * @return mixed
	 */
	public function __get(string $offset)
	{
		return $this->getValue($offset);
	}

}
