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

namespace Jyxo\Beholder\TestCase;

use Jyxo\Beholder\Result;
use Jyxo\Beholder\TestCase;
use function extension_loaded;
use function implode;
use function sprintf;

/**
 * Tests PHP extensions presence.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class PhpExtension extends TestCase
{

	/**
	 * List of extensions.
	 *
	 * @var array
	 */
	private $extensionList = [];

	/**
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param array $extensionList List of extensions
	 */
	public function __construct(string $description, array $extensionList)
	{
		parent::__construct($description);

		$this->extensionList = $extensionList;
	}

	/**
	 * Performs the test.
	 *
	 * @return Result
	 */
	public function run(): Result
	{
		// Check
		$missing = [];

		foreach ($this->extensionList as $extension) {
			if (!extension_loaded($extension)) {
				$missing[] = $extension;
			}
		}

		// Some extensions are missing
		if (!empty($missing)) {
			return new Result(Result::FAILURE, sprintf('Missing %s', implode(', ', $missing)));
		}

		// OK
		return new Result(Result::SUCCESS);
	}

}
