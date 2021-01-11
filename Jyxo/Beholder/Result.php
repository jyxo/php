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

namespace Jyxo\Beholder;

use InvalidArgumentException;
use function sprintf;

/**
 * Test result.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Matoušek
 * @author Jaroslav Hanslík
 */
class Result
{

	/**
	 * Success.
	 */
	public const SUCCESS = 'success';

	/**
	 * Failure.
	 */
	public const FAILURE = 'failure';

	/**
	 * Not-applicable test.
	 */
	public const NOT_APPLICABLE = 'not-applicable';

	/**
	 * Status.
	 *
	 * @var bool
	 */
	private $status;

	/**
	 * Description.
	 *
	 * @var string
	 */
	private $description = '';

	/**
	 * List of statuses.
	 *
	 * @var array
	 */
	private static $statusList = [
		self::SUCCESS => 'OK',
		self::FAILURE => 'FAILED',
		self::NOT_APPLICABLE => 'NOT APPLICABLE',
	];

	/**
	 * Result constructor.
	 *
	 * @param string $status Result status
	 * @param string $description Status description
	 */
	public function __construct(string $status, string $description = '')
	{
		// Checks status
		if (!isset(self::$statusList[$status])) {
			throw new InvalidArgumentException(sprintf('Invalid status %s', $status));
		}

		$this->status = $status;

		// Sets description
		if (empty($description)) {
			$description = self::$statusList[$status];
		}

		$this->description = $description;
	}

	/**
	 * Returns if the test was successful.
	 *
	 * @return bool
	 */
	public function isSuccess(): bool
	{
		return $this->status !== self::FAILURE;
	}

	/**
	 * Returns the test status.
	 *
	 * @return string
	 */
	public function getStatus(): string
	{
		return $this->status;
	}

	/**
	 * Returns the status message.
	 *
	 * @return string
	 */
	public function getStatusMessage(): string
	{
		return self::$statusList[$this->status];
	}

	/**
	 * Returns the description.
	 *
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

}
