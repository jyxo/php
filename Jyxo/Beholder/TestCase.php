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

/**
 * Base abstract beholder test class.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Matoušek
 * @author Jaroslav Hanslík
 */
abstract class TestCase
{

	/**
	 * Short one-line test description.
	 *
	 * @var string
	 */
	private $description = '';

	/**
	 * Performs the test.
	 *
	 * @return Result
	 */
	abstract public function run(): Result;

	/**
	 * Constructor.
	 *
	 * @param string $description Short description
	 */
	public function __construct(string $description)
	{
		$this->description = $description;
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
