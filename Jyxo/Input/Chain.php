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

use Jyxo\Input\Chain\Conditional;
use Jyxo\Input\Validator\ErrorMessage;
use function array_merge;

/**
 * Chain of filters a validators for a single variable.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class Chain implements ValidatorInterface
{

	/**
	 * Filter identifier.
	 */
	public const FILTER = 'filter';

	/**
	 * Validator identifier.
	 */
	public const VALIDATOR = 'validator';

	/**
	 * Array walk identifier.
	 */
	public const WALK = 'walk';

	/**
	 * Condition identifier.
	 */
	public const CONDITION = 'condition';

	/**
	 * Subchain closing identifier.
	 */
	public const CLOSE = 'close';

	/**
	 * Actual variable value.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Chain.
	 *
	 * @var array
	 */
	private $chain = [];

	/**
	 * Parent chain reference.
	 *
	 * @var Chain
	 */
	private $parent = null;

	/**
	 * Validation errors.
	 *
	 * @var array
	 */
	private $errors = [];

	/**
	 * Adds a validator to the chain.
	 *
	 * @param ValidatorInterface $validator Validator
	 * @param string $errorMessage Validation error message
	 * @return Chain
	 */
	public function addValidator(ValidatorInterface $validator, ?string $errorMessage = null): Chain
	{
		$this->chain[] = [self::VALIDATOR, $validator, $errorMessage];

		return $this;
	}

	/**
	 * Adds a filter to the chain.
	 *
	 * @param FilterInterface $filter Filter
	 * @return Chain
	 */
	public function addFilter(FilterInterface $filter): self
	{
		$this->chain[] = [self::FILTER, $filter];

		return $this;
	}

	/**
	 * Adds a new subchain and returns its instance.
	 *
	 * @return Chain
	 */
	public function addWalk(): self
	{
		$chain = new self();
		$chain->setParent($this);
		$this->chain[] = [self::WALK, $chain];

		return $chain;
	}

	/**
	 * Adds a new conditional subchain and returns its instance.
	 *
	 * @param Conditional $chain
	 * @return Conditional
	 */
	public function addCondition(Conditional $chain): self
	{
		$chain->setParent($this);
		$this->chain[] = [self::CONDITION, $chain];

		return $chain;
	}

	/**
	 * In case of a subchain returns its parent, the chain itself otherwise.
	 *
	 * @return Chain
	 */
	public function close(): self
	{
		if ($this->getParent() === null) {
			return $this;
		}

		return $this->getParent();
	}

	/**
	 * Returns if the chain contains any rules.
	 *
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return empty($this->chain);
	}

	/**
	 * Returns if the value is valid.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	public function isValid($value): bool
	{
		$success = $this->run($value);
		// $value passed by reference
		$this->value = $value;

		return $success;
	}

	/**
	 * Returns a filtered variable value.
	 *
	 * @return mixed
	 */
	public function &getValue()
	{
		return $this->value;
	}

	/**
	 * Returns a list of validation errors.
	 *
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * Returns the parent chain.
	 *
	 * @return Chain
	 */
	public function getParent(): self
	{
		return $this->parent;
	}

	/**
	 * Sets the parent chain.
	 *
	 * @param Chain $parent Parent chain
	 * @return Chain
	 */
	public function setParent(Chain $parent): self
	{
		$this->parent = $parent;

		return $this;
	}

	/**
	 * Starts filtering and validation.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	private function run(&$value): bool
	{
		foreach ($this->chain as $item) {
			if ($item[0] === self::FILTER) {
				/** @var FilterInterface $filter */
				$filter = $item[1];
				$value = $filter->filter($value);
			} elseif ($item[0] === self::VALIDATOR) {
				/** @var ValidatorInterface $validator */
				$validator = $item[1];

				if (!$validator->isValid($value)) {
					if ($validator instanceof ErrorMessage) {
						$this->errors[] = $validator->getError();
					} elseif (isset($item[2])) {
						$this->errors[] = $item[2];
					}

					return false;
				}
			} elseif ($item[0] === self::CONDITION) {
				/** @var Conditional $chain */
				$chain = $item[1];

				if (!$chain->isValid($value)) {
					$this->errors = array_merge($this->errors, $chain->getErrors());

					return false;
				}

				$value = $chain->getValue();
			} elseif ($item[0] === self::WALK) {
				/** @var Chain $chain */
				$chain = $item[1];

				foreach ($value as &$sub) {
					if (!$chain->isValid($sub)) {
						$this->errors = array_merge($this->errors, $chain->getErrors());

						return false;
					}

					$sub = $chain->getValue($sub);
				}
			}
		}

		return true;
	}

}
