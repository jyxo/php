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

use Jyxo\Input\ValidatorInterface;
use ReflectionClass;
use function array_slice;
use function func_get_args;

/**
 * Base abstract validator class.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
abstract class AbstractValidator implements ValidatorInterface
{

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	public static function validate($value): bool
	{
		$class = new ReflectionClass(static::class);
		$validator = $class->newInstanceArgs(array_slice(func_get_args(), 1));

		return $validator->isValid($value);
	}

}
