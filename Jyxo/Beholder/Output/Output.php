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
 * @category Jyxo
 * @package Jyxo\Beholder
 * @author Matěj Humpál
 */
abstract class Output
{

	protected $result;

	public function __construct(TestSuiteResult $result)
	{
		$this->result = $result;
	}

	public abstract function getContentType(): string;

	public abstract function __toString(): string;

	/**
	 * @return \Jyxo\Beholder\Result\TestSuiteResult
	 */
	public function getResult(): TestSuiteResult
	{
		return $this->result;
	}

}
