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

namespace Jyxo\Input\Validator;

/**
 * Validates a value using a custom callback or anonymous function.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Ondřej Nešpor
 */
class Callback extends \Jyxo\Input\Validator\AbstractValidator
{

	/**
	 * Validation callback.
	 *
	 * @var string|array|\Closure
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
	 * @param string|array|\Closure $callback Validation callback
	 */
	public function __construct($callback)
	{
		$this->setCallback($callback);
		$this->setAdditionalParams(array_slice(func_get_args(), 1));
	}

	/**
	 * Sets the validation callback.
	 *
	 * @param string|array|\Closure $callback Validation callback
	 * @return \Jyxo\Input\Validator\Callback
	 * @throws \Jyxo\Input\Validator\Exception On invalid callback definition
	 */
	public function setCallback($callback)
	{
		if (is_string($callback) || is_array($callback)) {
			if (!is_callable($callback)) {
				throw new Exception('Invalid callback definition');
			}
		} elseif (!is_object($callback) || !$callback instanceof \Closure) {
			throw new Exception('Invalid callback type; only string, array and \Closure instance are allowed');
		}

		$this->callback = $callback;

		return $this;
	}

	/**
	 * Returns the validation callback.
	 *
	 * @return string|array|\Closure
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * Sets additional validation callback parameters.
	 *
	 * @param array $params Parameters array
	 * @return \Jyxo\Input\Validator\Callback
	 */
	public function setAdditionalParams(array $params = [])
	{
		$this->additionalParams = $params;

		return $this;
	}

	/**
	 * Returns additional validation callback parameters.
	 *
	 * @return array
	 */
	public function getAdditionalParams()
	{
		return $this->additionalParams;
	}

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	public function isValid($value)
	{
		$params = $this->additionalParams;
		array_unshift($params, $value);
		return call_user_func_array($this->callback, $params);
	}

}
