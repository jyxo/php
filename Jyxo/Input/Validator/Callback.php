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

namespace Jyxo\Input\Validator;

use Closure;
use function array_slice;
use function array_unshift;
use function call_user_func_array;
use function func_get_args;

/**
 * Validates a value using a custom callback or anonymous function.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Ondřej Nešpor
 */
class Callback extends AbstractValidator
{

	/**
	 * Validation callback.
	 *
	 * @var string|array|Closure
	 */
	private $callback;

	/**
	 * Additional callback parameters.
	 *
	 * @var array
	 */
	private $additionalParams = [];

	/**
	 * Constructor.
	 *
	 * Optinally accepts additional parameters that will be used as additional callback parameters.
	 * The validated value will allways be used as the callback's first parameter.
	 *
	 * @param callable $callback Validation callback
	 */
	public function __construct(callable $callback)
	{
		$this->setCallback($callback);
		$this->setAdditionalParams(array_slice(func_get_args(), 1));
	}

	/**
	 * Sets the validation callback.
	 *
	 * @param callable $callback Validation callback
	 * @return Callback
	 */
	public function setCallback(callable $callback): self
	{
		$this->callback = $callback;

		return $this;
	}

	/**
	 * Returns the validation callback.
	 *
	 * @return string|array|Closure
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * Sets additional validation callback parameters.
	 *
	 * @param array $params Parameters array
	 * @return Callback
	 */
	public function setAdditionalParams(array $params = []): self
	{
		$this->additionalParams = $params;

		return $this;
	}

	/**
	 * Returns additional validation callback parameters.
	 *
	 * @return array
	 */
	public function getAdditionalParams(): array
	{
		return $this->additionalParams;
	}

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	public function isValid($value): bool
	{
		$params = $this->additionalParams;
		array_unshift($params, $value);

		return call_user_func_array($this->callback, $params);
	}

}
