<?php

/**
 * Testing validator with a prefix.
 */
namespace SomeOtherPrefix\Some;

class Validator {

	public static function isNumeric($value) {
		return is_numeric($value);
	}

}
