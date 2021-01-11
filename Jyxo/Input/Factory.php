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

use ReflectionClass;
use function array_unshift;
use function class_exists;
use function count;
use function reset;
use function sprintf;
use function ucfirst;

/**
 * \Jyxo\Input objects factory.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class Factory
{

	/**
	 * Filter class names prefix.
	 *
	 * @var string
	 */
	private static $filterPrefix = [
		'\Jyxo\Input\Filter\\',
	];

	/**
	 * Validator class names prefix.
	 *
	 * @var string
	 */
	private static $validatorPrefix = [
		'\Jyxo\Input\Validator\\',
	];

	/**
	 * Returns a particular validator by its name.
	 *
	 * @param string|ValidatorInterface $name Validator name
	 * @param mixed|array $param Validator constructor parameters. In case of a single parameter it can be its value, an array of values otherwise. NULL in case of no parameter.
	 * @return ValidatorInterface
	 */
	public function getValidatorByName($name, $param = null): ValidatorInterface
	{
		if ($name instanceof ValidatorInterface) {
			return $name;
		}

		$params = (array) $param;

		$className = $this->findClass($name, self::$validatorPrefix);

		if (!$className) {
			throw new Exception(sprintf('Could not found "%s" validator', $name));
		}

		return $this->getClass($className, $params);
	}

	/**
	 * Returns a particular filter by its name.
	 *
	 * @param string|FilterInterface $name Filter name
	 * @param mixed|array $param Filter constructor parameters. In case of a single parameter it can be its value, an array of values otherwise. NULL in case of no parameter.
	 * @return FilterInterface
	 */
	public function getFilterByName($name, $param = null): FilterInterface
	{
		if ($name instanceof FilterInterface) {
			return $name;
		}

		$params = (array) $param;

		$className = $this->findClass($name, self::$filterPrefix);

		if (!$className) {
			throw new Exception(sprintf('Could not found "%s" filter', $name));
		}

		return $this->getClass($className, $params);
	}

	/**
	 * Registers a new validator prefix.
	 *
	 * The backslash at the end is required; e.g. for class "\Api\IsInt" the prefix would be "\Api\" and validator name "IsInt".
	 *
	 * @param string $prefix Validator class prefix
	 */
	public static function addValidatorPrefix(string $prefix): void
	{
		array_unshift(self::$validatorPrefix, $prefix);
	}

	/**
	 * Registers a new filter prefix.
	 *
	 * The backslash at the end is required; e.g. for class "\Api\ToInt" the prefix would be "\Api\" a filter name "ToInt".
	 *
	 * @param string $prefix
	 */
	public static function addFilterPrefix(string $prefix): void
	{
		array_unshift(self::$filterPrefix, $prefix);
	}

	/**
	 * Finds a class by its name and possible prefixes.
	 *
	 * Returns the first found or NULL if no corresponding class was found.
	 *
	 * @param string $name Class name
	 * @param array $prefixes Class prefixes
	 * @return string|null
	 */
	private function findClass(string $name, array $prefixes): ?string
	{
		$className = null;
		$name = ucfirst($name);

		foreach ($prefixes as $prefix) {
			$tempName = $prefix . $name;

			if (class_exists($tempName)) {
				$className = $tempName;

				break;
			}
		}

		return $className;
	}

	/**
	 * Creates a class instance with an arbitrary number of parameters.
	 *
	 * @param string $className Class name
	 * @param array $params Parameters array
	 * @return object
	 */
	private function getClass(string $className, array $params)
	{
		$instance = null;

		switch (count($params)) {
			case 0:
				$instance = new $className();

				break;
			case 1:
				$instance = new $className(reset($params));

				break;
			default:
				$reflection = new ReflectionClass($className);
				$instance = $reflection->newInstanceArgs($params);

				break;
		}

		return $instance;
	}

}
