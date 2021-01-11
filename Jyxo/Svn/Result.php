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

namespace Jyxo\Svn;

use Countable;
use SeekableIterator;
use function count;
use function explode;
use function preg_match;
use function sprintf;
use function substr;
use function trim;

/**
 * Container for parsed SVN binary output.
 *
 * Experimental.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Matěj Humpál
 */
class Result implements Countable, SeekableIterator
{

	/**
	 * OK status.
	 */
	public const OK = 'OK';

	/**
	 * KO status.
	 */
	public const KO = 'KO';

	/**
	 * Added file flag.
	 */
	public const ADD = 'A';

	/**
	 * Deleted file flag.
	 */
	public const DELETE = 'D';

	/**
	 * Updated file flag.
	 */
	public const UPDATE = 'U';

	/**
	 * Conflicted file flag.
	 */
	public const CONFLICT = 'C';

	/**
	 * Modified file flag.
	 */
	public const MODIFIED = 'M';

	/**
	 * Merged file flag.
	 */
	public const MERGE = 'G';

	/**
	 * SVN:externals file flag.
	 */
	public const EXTERNALS = 'X';

	/**
	 * Ignored file flag.
	 */
	public const IGNORED = 'I';

	/**
	 * Locked file flag.
	 */
	public const LOCKED = 'L';

	/**
	 * Non-versioned file flag.
	 */
	public const NOT_VERSIONED = '?';

	/**
	 * Missing file flag.
	 */
	public const MISSING = '!';

	/**
	 * Flag meaning that the versioned object (file, directory, ...)
	 * has been replaced with another kind of object.
	 */
	public const DIR_FILE_SWITCH = '~';

	/**
	 * History scheduled with commit flag.
	 */
	public const SCHEDULED = '+';

	/**
	 * Switched item flag.
	 */
	public const SWITCHED = 'S';

	/**
	 * Flag meaning that there is a newer version on the server.
	 */
	public const NEW_VERSION_EXISTS = '*';

	/**
	 * Status table.
	 *
	 * @var array
	 */
	protected $statusTable = [
		self::ADD => 'A',
		self::DELETE => 'D',
		self::UPDATE => 'U',
		self::CONFLICT => 'C',
		self::MODIFIED => 'M',
		self::MERGE => 'G',
		self::EXTERNALS => 'X',
		self::IGNORED => 'I',
		self::LOCKED => 'L',
		self::NOT_VERSIONED => '?',
		self::MISSING => '!',
		self::DIR_FILE_SWITCH => '',
		self::SCHEDULED => '+',
		self::SWITCHED => 'S',
		self::NEW_VERSION_EXISTS => '*',
	];

	/**
	 * Action revision.
	 *
	 * @var int
	 */
	protected $revision;

	/**
	 * Action error.
	 *
	 * @var string
	 */
	protected $error = '';

	/**
	 * Action status.
	 *
	 * @var string
	 */
	protected $status;

	/**
	 * Action items.
	 *
	 * @var array
	 */
	protected $items = [];

	/**
	 * Internal pointer.
	 *
	 * @var int
	 */
	protected $pointer = 0;

	/**
	 * Constructor.
	 *
	 * @param string $action SVN action
	 * @param string $input Action input
	 * @param int $returnCode SVN binary return code
	 */
	public function __construct(string $action, string $input, int $returnCode = 1)
	{
		$this->items = $this->parse($action, $input);
		$this->status = $returnCode === 0 ? self::OK : self::KO;
		$this->error = $returnCode === 0 ? '' : $input;
	}

	/**
	 * Moves the internal pointer to the given position.
	 *
	 * @param int $position New pointer position
	 * @return Result
	 */
	public function seek(int $position): self
	{
		$position = (int) $position;

		if ($position < 0 || $position > count($this->items)) {
			throw new Exception(sprintf('Illegal index %d', $position));
		}

		$this->pointer = $position;

		return $this;
	}

	/**
	 * Returns an item on the actual pointer position.
	 *
	 * @return mixed
	 */
	public function current()
	{
		if ($this->valid()) {
			return $this->items[$this->pointer];
		}

		return null;
	}

	/**
	 * Advances internal pointer's position to the next item.
	 *
	 * @return bool
	 */
	public function next(): bool
	{
		// phpcs:disable SlevomatCodingStandard.Operators.DisallowIncrementAndDecrementOperators.DisallowedPreIncrementOperator
		// phpcs:disable SlevomatCodingStandard.Operators.RequireOnlyStandaloneIncrementAndDecrementOperators.PreIncrementOperatorNotUsedStandalone
		return ++$this->pointer < count($this->items);
	}

	/**
	 * Moves the internal pointer to the beginning.
	 *
	 * @return Result
	 */
	public function rewind(): Result
	{
		$this->pointer = 0;

		return $this;
	}

	/**
	 * Returns the current key value.
	 *
	 * @return string|null
	 */
	public function key(): ?string
	{
		return $this->items[$this->pointer];
	}

	/**
	 * Checks if the internal pointer is within correct boundaries.
	 *
	 * @return bool
	 */
	public function valid(): bool
	{
		return $this->pointer < count($this->items);
	}

	/**
	 * Returns item count.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * Parses SVN binary output according to the action.
	 *
	 * @param string $action SVN action
	 * @param string $input SVN binary output
	 * @return array
	 */
	protected function parse(string $action, string $input): array
	{
		switch ($action) {
			case 'add':
			case 'status':
				return $this->parseStatus($input);
			case 'commit':
				return $this->parseCommit($input);
			case 'update':
				return $this->parseUpdate($input);
			default:
				// Do nothing
				return [];
		}
	}

	/**
	 * Parses SVN statis.
	 *
	 * @param string $input SVN binary output
	 * @return array
	 */
	protected function parseStatus(string $input): array
	{
		$array = explode("\n", (string) $input);

		foreach ($array as $key => &$line) {

			$line = trim($line);

			if (empty($line)) {
				unset($array[$key]);

				continue;
			}

			$tmp = $line;
			$line = [];

			if ($tmp[0] !== ' ') {
				$line['status'] = $tmp[0];
			}

			if ($tmp[1] !== ' ') {
				$line['properties'] = $tmp[1];
			}

			if ($tmp[2] !== ' ') {
				$line['lock'] = $tmp[2];
			}

			if ($tmp[3] !== ' ') {
				$line['history'] = $tmp[3];
			}

			if ($tmp[4] !== ' ') {
				$line['switch'] = $tmp[4];
			}

			$line['file'] = substr($tmp, 7);

		}

		return $array;
	}

	/**
	 * Parses commit output and sets revision number.
	 *
	 * @param string $input SVN binary output
	 * @return array
	 */
	protected function parseCommit(string $input): array
	{
		$array = explode("\n", (string) $input);

		foreach ($array as $key => &$line) {

			$line = trim($line);

			if (empty($line)) {
				unset($array[$key]);

				continue;
			}

			if (preg_match('/Committed revision ([0-9]+)\./i', $line, $matches)) {
				$this->revision = (int) $matches[1];
				unset($array[$key]);

				continue;
			}

			if (!preg_match('/Sending.*/', $line)) {
				unset($array[$key]);

				continue;
			}
		}

		return $array;
	}

	/**
	 * Parses update output.
	 *
	 * @param mixed $input SVN binary output
	 * @return array
	 */
	protected function parseUpdate($input): array
	{
		$array = explode("\n", (string) $input);

		foreach ($array as $key => &$line) {

			$line = trim($line);

			if (empty($line)) {
				unset($array[$key]);

				continue;
			}

			if (preg_match('/At revision ([0-9]+)\./i', $line, $matches)) {
				$this->revision = (int) $matches[1];
				unset($array[$key]);

				continue;
			}
		}

		return $array;
	}

	/**
	 * Magic __get method.
	 *
	 * @param string $prop Property name
	 * @return mixed
	 */
	public function __get(string $prop)
	{
		return $this->$prop ?? null;
	}

}
