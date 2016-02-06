<?php declare(strict_types = 1);

/**
 * Testing validator with a prefix.
 */
namespace SomeOtherPrefix\Some;

class Validator implements \Jyxo\Input\ValidatorInterface
{

	public function isValid($value)
	{
		return is_numeric($value);
	}

}
