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
use function file_get_contents;
use function file_put_contents;
use function md5;
use function sprintf;
use function strlen;
use function time;
use function uniqid;
use function unlink;

/**
 * Filesystem access test.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class FileSystem extends TestCase
{

	/**
	 * Tested directory.
	 *
	 * @var string
	 */
	private $dir;

	/**
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param string $dir Tested directory
	 */
	public function __construct(string $description, string $dir)
	{
		parent::__construct($description);

		$this->dir = $dir;
	}

	/**
	 * Performs the test.
	 *
	 * @return Result
	 */
	public function run(): Result
	{
		$random = md5(uniqid((string) time(), true));
		$path = $this->dir . '/beholder-' . $random . '.txt';
		$content = $random;

		// Writing
		if (!file_put_contents($path, $content)) {
			return new Result(Result::FAILURE, sprintf('Write error %s', $this->dir));
		}

		// Reading
		$readContent = file_get_contents($path);

		if (strlen($readContent) !== strlen($content)) {
			return new Result(Result::FAILURE, sprintf('Read error %s', $this->dir));
		}

		// Deleting
		if (!@unlink($path)) {
			return new Result(Result::FAILURE, sprintf('Delete error %s', $this->dir));
		}

		// OK
		return new Result(Result::SUCCESS, $this->dir);
	}

}
