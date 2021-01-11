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

namespace Jyxo\Input\Chain;

use Jyxo\Input\Chain;
use Jyxo\Input\ValidatorInterface;

/**
 * Chain of filters and validators for a single variable.
 * The validation itself is performed after fulfilling the condition.
 * The condition is checked by a defined validator.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Roman Řáha
 */
class Conditional extends Chain
{

	/**
	 * Condition validator.
	 *
	 * @var ValidatorInterface
	 */
	private $condValidator = null;

	/**
	 * Sets the condition validator.
	 *
	 * @param ValidatorInterface $validator
	 */
	public function __construct(?ValidatorInterface $validator = null)
	{
		if ($validator !== null) {
			$this->setCondValidator($validator);
		}
	}

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	public function isValid($value): bool
	{
		// Validation is performed only if the condition is fulfilled
		if ($this->checkCondition($value) === true) {
			return parent::isValid($value);
		}

		// No validation -> the value is valid
		$this->value = $value;

		return true;
	}

	/**
	 * Sets the condition validator.
	 *
	 * @param ValidatorInterface $validator Condition validator
	 * @return Conditional
	 */
	public function setCondValidator(ValidatorInterface $validator): self
	{
		$this->condValidator = $validator;

		return $this;
	}

	/**
	 * Checks if the condition is fulfilled.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	private function checkCondition($value): bool
	{
		if ($this->condValidator === null) {
			// There is no validator -> always fulfilled
			return true;
		}

		return $this->condValidator->isValid($value);
	}

}
