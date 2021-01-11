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

namespace Jyxo\Time;

use Jyxo\Exception;
use function in_array;

/**
 * Exception used by the \Jyxo\Time\Composer.
 *
 * Stores information which unit was incorrectly provided.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Martin Šamšula
 */
class ComposerException extends Exception
{

	/**
	 * Exception type - second.
	 */
	public const SECOND = 1;

	/**
	 * Exception type - minute.
	 */
	public const MINUTE = 2;

	/**
	 * Exception type - hour.
	 */
	public const HOUR = 3;

	/**
	 * Exception type - day.
	 */
	public const DAY = 4;

	/**
	 * Exception type - month.
	 */
	public const MONTH = 5;

	/**
	 * Exception type - year.
	 */
	public const YEAR = 6;

	/**
	 * Exception type - incomplete date.
	 */
	public const NOT_COMPLETE = 7;

	/**
	 * Exception type - invalid date.
	 */
	public const INVALID = 8;

	/**
	 * Exception type - unknown.
	 */
	public const UNKNOWN = 0;

	/**
	 * Constructor.
	 *
	 * @param string $message Exception message
	 * @param int $code Exception code (type)
	 */
	public function __construct(string $message, int $code)
	{
		static $allowedUnits = [
			self::SECOND,
			self::MINUTE,
			self::HOUR,
			self::DAY,
			self::MONTH,
			self::YEAR,
			self::INVALID,
			self::NOT_COMPLETE,
		];

		if (!in_array($code, $allowedUnits, true)) {
			$code = self::UNKNOWN;
		}

		parent::__construct($message, $code);
	}

}
