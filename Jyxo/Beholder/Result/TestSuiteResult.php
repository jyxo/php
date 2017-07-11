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

namespace Jyxo\Beholder\Result;

/**
 * Beholder test suite result value object
 *
 * @category Jyxo
 * @package Jyxo\Beholder
 * @author Matěj Humpál
 */
class TestSuiteResult
{

	/**
	 * @var string
	 */
	private $project;

	/**
	 * @var bool
	 */
	private $allSucceeded;

	/**
	 * @var array
	 */
	private $testsData;

	/**
	 * @var string
	 */
	private $includeFilter;

	/**
	 * @var string
	 */
	private $excludeFilter;

	/**
	 * @param string $project
	 * @param bool $allSucceeded
	 * @param array $testsData
	 * @param string $includeFilter
	 * @param string $excludeFilter
	 */
	public function __construct(string $project, bool $allSucceeded, array $testsData, string $includeFilter, string $excludeFilter)
	{
		$this->project = $project;
		$this->allSucceeded = $allSucceeded;
		$this->testsData = $testsData;
		$this->includeFilter = $includeFilter;
		$this->excludeFilter = $excludeFilter;
	}

	/**
	 * @return string
	 */
	public function getProject(): string
	{
		return $this->project;
	}

	/**
	 * @return bool
	 */
	public function hasAllSucceeded(): bool
	{
		return $this->allSucceeded;
	}

	/**
	 * @return array
	 */
	public function getTestsData(): array
	{
		return $this->testsData;
	}

	/**
	 * @return string
	 */
	public function getIncludeFilter(): string
	{
		return $this->includeFilter;
	}

	/**
	 * @return string
	 */
	public function getExcludeFilter(): string
	{
		return $this->excludeFilter;
	}

}
