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

namespace Jyxo\Input;

/**
 * Chain of filters a validators for a single variable.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Chain
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class Chain implements \Jyxo\Input\ValidatorInterface
{
	/**
	 * Filter identifier.
	 *
	 * @var string
	 */
	const FILTER = 'filter';

	/**
	 * Validator identifier.
	 *
	 * @var string
	 */
	const VALIDATOR = 'validator';

	/**
	 * Array walk identifier.
	 *
	 * @var string
	 */
	const WALK = 'walk';

	/**
	 * Condition identifier.
	 *
	 * @var string
	 */
	const CONDITION = 'condition';

	/**
	 * Subchain closing identifier.
	 *
	 * @var string
	 */
	const CLOSE = 'close';

	/**
	 * Chain.
	 *
	 * @var array
	 */
	private $chain = array();

	/**
	 * Parent chain reference.
	 *
	 * @var \Jyxo\Input\Chain
	 */
	private $parent = null;

	/**
	 * Actual variable value.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Validation errors.
	 *
	 * @var array(string)
	 */
	private $errors = array();

	/**
	 * Adds a validator to the chain.
	 *
	 * @param \Jyxo\Input\ValidatorInterface $validator Validator
	 * @param string $errorMessage Validation error message
	 * @return \Jyxo\Input\Chain
	 */
	public function addValidator(\Jyxo\Input\ValidatorInterface $validator, $errorMessage = null)
	{
		$this->chain[] = array(self::VALIDATOR, $validator, $errorMessage);
		return $this;
	}

	/**
	 * Adds a filter to the chain.
	 *
	 * @param \Jyxo\Input\FilterInterface $filter Filter
	 * @return \Jyxo\Input\Chain
	 */
	public function addFilter(\Jyxo\Input\FilterInterface $filter)
	{
		$this->chain[] = array(self::FILTER, $filter);
		return $this;
	}

	/**
	 * Adds a new subchain and returns its instance.
	 *
	 * @return \Jyxo\Input\Chain
	 */
	public function addWalk()
	{
		$chain = new self();
		$chain->setParent($this);
		$this->chain[] = array(self::WALK, $chain);
		return $chain;
	}

	/**
	 * Adds a new conditional subchain and returns its instance.
	 *
	 * @param \Jyxo\Input\Chain\Conditional $chain
	 * @return \Jyxo\Input\Chain\Conditional
	 */
	public function addCondition(\Jyxo\Input\Chain\Conditional $chain)
	{
		$chain->setParent($this);
		$this->chain[] = array(self::CONDITION, $chain);
		return $chain;
	}

	/**
	 * In case of a subchain returns its parent, the chain itself otherwise.
	 *
	 * @return \Jyxo\Input\Chain
	 */
	public function close()
	{
		if (null === $this->getParent()) {
			return $this;
		}
		return $this->getParent();
	}

	/**
	 * Starts filtering and validation.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	private function run(&$value)
	{
		foreach ($this->chain as $item) {
			if (self::FILTER === $item[0]) {
				$filter = $item[1];
				/* @var $filter \Jyxo\Input\FilterInterface */
				$value = $filter->filter($value);
			} elseif (self::VALIDATOR === $item[0]) {
				$validator = $item[1];
				/* @var $validator \Jyxo\Input\ValidatorInterface */
				if (!$validator->isValid($value)) {
					if ($validator instanceof \Jyxo\Input\Validator\ErrorMessage) {
						$this->errors[] = $validator->getError();
					} elseif (isset($item[2])) {
						$this->errors[] = $item[2];
					}
					return false;
				}
			} elseif (self::CONDITION === $item[0]) {
				$chain = $item[1];
				/* @var $chain \Jyxo\Input\Chain\Conditional */
				if ($chain->isValid($value)) {
					$value = $chain->getValue();
				} else {
					$this->errors = array_merge($this->errors, $chain->getErrors());
					return false;
				}
			} elseif (self::WALK === $item[0]) {
				$chain = $item[1];
				/* @var $chain \Jyxo\Input\Chain */
				foreach ($value as &$sub) {
					if ($chain->isValid($sub)) {
						$sub = $chain->getValue($sub);
					} else {
						$this->errors = array_merge($this->errors, $chain->getErrors());
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Returns if the chain contains any rules.
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
		return empty($this->chain);
	}

	/**
	 * Returns if the value is valid.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	public function isValid($value)
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
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Returns the parent chain.
	 *
	 * @return \Jyxo\Input\Chain
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Sets the parent chain.
	 *
	 * @param \Jyxo\Input\Chain $parent Parent chain
	 * @return \Jyxo\Input\Chain
	 */
	public function setParent(\Jyxo\Input\Chain $parent)
	{
		$this->parent = $parent;
		return $this;
	}
}
