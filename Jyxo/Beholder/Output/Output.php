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

namespace Jyxo\Beholder\Output;

use Jyxo\Beholder\Result\TestSuiteResult;

/**
 * Beholder output base class
 *
 * @author Matěj Humpál
 */
abstract class Output
{

	/** @var TestSuiteResult */
	protected $result;

	abstract public function getContentType(): string;

	public function __construct(TestSuiteResult $result)
	{
		$this->result = $result;
	}

	public function getResult(): TestSuiteResult
	{
		return $this->result;
	}

	abstract public function __toString(): string;

}
