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

namespace Jyxo\Svn;

/**
 * Container for parsed SVN binary output.
 *
 * Experimental.
 *
 * @category Jyxo
 * @package Jyxo\Svn
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Matěj Humpál
 */
class Result implements \Countable, \SeekableIterator
{

	/**
	 * OK status.
	 *
	 * @var string
	 */
	const OK = 'OK';

	/**
	 * KO status.
	 *
	 * @var string
	 */
	const KO = 'KO';

	/**
	 * Added file flag.
	 *
	 * @var string
	 */
	const ADD = 'A';

	/**
	 * Deleted file flag.
	 *
	 * @var string
	 */
	const DELETE = 'D';

	/**
	 * Updated file flag.
	 *
	 * @var string
	 */
	const UPDATE = 'U';

	/**
	 * Conflicted file flag.
	 *
	 * @var string
	 */
	const CONFLICT = 'C';

	/**
	 * Modified file flag.
	 *
	 * @var string
	 */
	const MODIFIED = 'M';

	/**
	 * Merged file flag.
	 *
	 * @var string
	 */
	const MERGE = 'G';

	/**
	 * SVN:externals file flag.
	 *
	 * @var string
	 */
	const EXTERNALS = 'X';

	/**
	 * Ignored file flag.
	 *
	 * @var string
	 */
	const IGNORED = 'I';

	/**
	 * Locked file flag.
	 *
	 * @var string
	 */
	const LOCKED = 'L';

	/**
	 * Non-versioned file flag.
	 *
	 * @var string
	 */
	const NOT_VERSIONED = '?';

	/**
	 * Missing file flag.
	 *
	 * @var string
	 */
	const MISSING = '!';

	/**
	 * Flag meaning that the versioned object (file, directory, ...)
	 * has been replaced with another kind of object.
	 *
	 * @var string
	 */
	const DIR_FILE_SWITCH = '~';

	/**
	 * History scheduled with commit flag.
	 *
	 * @var string
	 */
	const SCHEDULED = '+';

	/**
	 * Switched item flag.
	 *
	 * @var string
	 */
	const SWITCHED = 'S';

	/**
	 * Flag meaning that there is a newer version on the server.
	 *
	 * @var string
	 */
	const NEW_VERSION_EXISTS = '*';

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
	 * @var integer
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
	 * @var integer
	 */
	protected $pointer = 0;

	/**
	 * Constructor.
	 *
	 * @param string $action SVN action
	 * @param string $input Action input
	 * @param integer $returnCode SVN binary return code
	 */
	public function __construct($action, $input, $returnCode = 1)
	{
		$this->items = $this->parse($action, $input);
		$this->status = $returnCode === 0 ? self::OK : self::KO;
		$this->error = $returnCode === 0 ? '' : $input;
	}

	/**
	 * Parses SVN binary output according to the action.
	 *
	 * @param string $action SVN action
	 * @param string $input SVN binary output
	 * @return array
	 */
	protected function parse($action, $input)
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
	protected function parseStatus($input)
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

			if ($tmp{0} !== ' ') {
				$line['status'] = $tmp{0};
			}
			if ($tmp{1} !== ' ') {
				$line['properties'] = $tmp{1};
			}
			if ($tmp{2} !== ' ') {
				$line['lock'] = $tmp{2};
			}
			if ($tmp{3} !== ' ') {
				$line['history'] = $tmp{3};
			}
			if ($tmp{4} !== ' ') {
				$line['switch'] = $tmp{4};
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
	protected function parseCommit($input)
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
	protected function parseUpdate($input)
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
	public function __get($prop)
	{
		return isset($this->$prop) ? $this->$prop : null;
	}

	/**
	 * Moves the internal pointer to the given position.
	 *
	 * @param integer $position New pointer position
	 * @return \Jyxo\Svn\Result
	 * @throws \Jyxo\Svn\Exception On invalid position
	 */
	public function seek($position)
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
	 * @return boolean
	 */
	public function next()
	{
		return ++$this->pointer < count($this->items);
	}

	/**
	 * Moves the internal pointer to the beginning.
	 *
	 * @return \Jyxo\Svn\Result
	 */
	public function rewind()
	{
		$this->pointer = 0;

		return $this;
	}

	/**
	 * Returns the current key value.
	 *
	 * @return null|string
	 */
	public function key()
	{
		return $this->items[$this->pointer];
	}

	/**
	 * Checks if the internal pointer is within correct boundaries.
	 *
	 * @return boolean
	 */
	public function valid()
	{
		return $this->pointer < count($this->items);
	}

	/**
	 * Returns item count.
	 *
	 * @return integer
	 */
	public function count()
	{
		return count($this->items);
	}
}
