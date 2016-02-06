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

namespace Jyxo\Time;

/**
 * Exception used by the \Jyxo\Time\Composer.
 *
 * Stores information which unit was incorrectly provided.
 *
 * @category Jyxo
 * @package Jyxo\Time
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Martin Šamšula
 */
class ComposerException extends \Jyxo\Exception
{
	/**
	 * Exception type - second.
	 *
	 * @var integer
	 */
	const SECOND = 1;

	/**
	 * Exception type - minute.
	 *
	 * @var integer
	 */
	const MINUTE = 2;

	/**
	 * Exception type - hour.
	 *
	 * @var integer
	 */
	const HOUR = 3;

	/**
	 * Exception type - day.
	 *
	 * @var integer
	 */
	const DAY = 4;

	/**
	 * Exception type - month.
	 *
	 * @var integer
	 */
	const MONTH = 5;

	/**
	 * Exception type - year.
	 *
	 * @var integer
	 */
	const YEAR = 6;

	/**
	 * Exception type - incomplete date.
	 *
	 * @var integer
	 */
	const NOT_COMPLETE = 7;

	/**
	 * Exception type - invalid date.
	 *
	 * @var integer
	 */
	const INVALID = 8;

	/**
	 * Exception type - unknown.
	 *
	 * @var integer
	 */
	const UNKNOWN = 0;

	/**
	 * Constructor.
	 *
	 * @param string $message Exception message
	 * @param integer $code Exception code (type)
	 */
	public function __construct($message, $code)
	{
		static $allowedUnits = [
			self::SECOND,
			self::MINUTE,
			self::HOUR,
			self::DAY,
			self::MONTH,
			self::YEAR,
			self::INVALID,
			self::NOT_COMPLETE
		];

		if (!in_array($code, $allowedUnits)) {
			$code = self::UNKNOWN;
		}

		parent::__construct($message, $code);
	}
}
