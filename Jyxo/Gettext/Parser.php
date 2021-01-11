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

namespace Jyxo\Gettext;

use Countable;
use Iterator;
use Jyxo\Gettext\Parser\Exception;
use Jyxo\Gettext\Parser\Header;
use Jyxo\Gettext\Parser\Item;
use function array_key_exists;
use function array_shift;
use function count;
use function file;
use function sprintf;
use function strtolower;
use function substr;

/**
 * Parses Gettext PO files.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Matěj Humpál
 */
class Parser implements Iterator, Countable
{

	/**
	 * Path to the parsed PO file.
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * PO file header (copyright and other information).
	 *
	 * @var string
	 */
	protected $header;

	/**
	 * Fragments parsed from the PO file.
	 *
	 * @var array of \Jyxo\Gettext\Parser\Item
	 */
	protected $items = [];

	/**
	 * Internal pointer to the fragments array.
	 *
	 * @var int
	 */
	protected $current = 0;

	/**
	 * Parser class name.
	 *
	 * Useful for subclassing.
	 *
	 * @var string
	 */
	protected $itemClass = Item::class;

	/**
	 * Header parser class name.
	 *
	 * Useful for subclassing.
	 *
	 * @var string
	 */
	protected $headerClass = Header::class;

	/**
	 * Constructor.
	 *
	 * Loads and parses the given file.
	 *
	 * @param string $file Path to the PO file.
	 */
	public function __construct(string $file)
	{
		$this->parse($file);
	}

	/**
	 * {@link \Countable} interface method
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * {@link \ArrayIterator} interface method.
	 *
	 * @return mixed
	 */
	public function current()
	{
		return $this->items[$this->current];
	}

	/**
	 * {@link \ArrayIterator} interface method.
	 *
	 * @return mixed
	 */
	public function key()
	{
		return $this->current;
	}

	/**
	 * {@link \ArrayIterator} interface method.
	 */
	public function next(): void
	{
		$this->current++;
	}

	/**
	 * {@link \ArrayIterator} interface method.
	 */
	public function rewind(): void
	{
		$this->current = 0;
	}

	/**
	 * {@link \ArrayIterator} interface method.
	 *
	 * @return bool
	 */
	public function valid(): bool
	{
		return isset($this->items[$this->current]);
	}

	/**
	 * The actual parser.
	 *
	 * Walks through the file, splits it on empty lines and tries to parse each
	 * fragment using the defined parser class ({@link \Jyxo\Gettext\Parser\Item} by default).
	 *
	 * Does not work with the file header.
	 *
	 * @see \Jyxo\Gettext\Parser::$items
	 * @see \Jyxo\Gettext\Parser\Item
	 * @param string $file Path to the PO file
	 */
	protected function parse(string $file): void
	{
		$linenumber = 0;
		$chunks = [];

		$file = file($file);
		foreach ($file as $line) {
			if ($line === "\n" || $line === "\r\n") {
				$linenumber++;
			} else {
				if (!array_key_exists($linenumber, $chunks)) {
					$chunks[$linenumber] = '';
				}
				$chunks[$linenumber] .= $line;
			}
		}

		$header = array_shift($chunks);
		$this->header = new $this->headerClass($header);

		foreach ($chunks as $chunk) {
			try {
				$this->items[] = new $this->itemClass($chunk);
			} catch (Exception $e) {
				// Do nothing, msgid is empty
			}
		}
	}

	/**
	 * Method overloading.
	 *
	 * Makes getProperty methods available for retrieving property values
	 * Makes setProperty methods available for setting property values
	 *
	 * @param string $name Method name
	 * @param array $args Method parameters
	 * @return mixed Value of variable or \Jyxo\Gettext\Parser
	 * @throws Exception Non-existing method
	 */
	public function __call(string $name, array $args)
	{
		if (substr($name, 0, 3) === 'get') {
			$var = substr($name, 3);
			if (!empty($var)) {
				$var = strtolower(substr($var, 0, 1)) . substr($var, 1);
				if (!isset($this->$var)) {
					throw new Parser\Exception(
						sprintf('Non-existing method %s::%s() called in %s, line %s', self::class, $name, __FILE__, __LINE__)
					);
				}

				return $this->$var;
			}
		}

		if (substr($name, 0, 3) !== 'set') {
			return;
		}

		$var = substr($name, 3);
		if (!empty($var)) {
			$var = strtolower(substr($var, 0, 1)) . substr($var, 1);
			if (!isset($this->$var)) {
				throw new Parser\Exception(
					sprintf('Non-existing method %s::%s() called in %s, line %s', self::class, $name, __FILE__, __LINE__)
				);
			}

			$this->$var = $args[0];

			return $this;
		}
	}

}
