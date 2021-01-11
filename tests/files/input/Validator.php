<?php declare(strict_types = 1);

/**
 * Testing validator with a prefix.
 */

namespace SomeOtherPrefix\Some;

use Jyxo\Input\ValidatorInterface;
use function is_numeric;

class Validator implements ValidatorInterface
{

	public function isValid($value): bool
	{
		return is_numeric($value);
	}

}
