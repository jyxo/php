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

namespace Jyxo\Gettext;

/**
 * Parses Gettext PO files.
 *
 * @category Jyxo
 * @package Jyxo\Gettext
 * @subpackage Parser
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Matěj Humpál
 */
class Parser implements \Iterator, \Countable
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
	protected $items = array();

	/**
	 * Internal pointer to the fragments array.
	 *
	 * @var integer
	 */
	protected $current = 0;

	/**
	 * Parser class name.
	 *
	 * Useful for subclassing.
	 *
	 * @var string
	 */
	protected $itemClass = '\Jyxo\Gettext\Parser\Item';

	/**
	 * Header parser class name.
	 *
	 * Useful for subclassing.
	 *
	 * @var string
	 */
	protected $headerClass = '\Jyxo\Gettext\Parser\Header';

	/**
	 * Constructor.
	 *
	 * Loads and parses the given file.
	 *
	 * @param string $file Path to the PO file.
	 */
	public function __construct($file)
	{
		$this->parse($file);
	}

	/**
	 * The actual parser.
	 *
	 * Walks through the file, splits it on empty lines and tries to parse each
	 * fragment using the defined parser class (\Jyxo\Gettext\Parser\Item by default).
	 *
	 * Does not work with the file header.
	 *
	 * @param string $file Path to the PO file
	 * @see \Jyxo\Gettext\Parser::$items
	 * @see \Jyxo\Gettext\Parser\Item
	 */
	protected function parse($file)
	{
		$linenumber = 0;
		$chunks = array();

		$file = file($file);
		foreach ($file as $line) {
			if ($line == "\n" || $line == "\r\n") {
				++$linenumber;
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
			} catch (\Jyxo\Gettext\Parser\Exception $e) {
				// Do nothing, msgid is empty
			}
		}

	}

	/**
	 * \Countable interface method
	 *
	 * @return boolean
	 */
	public function count()
	{
		return count($this->items);
	}

	/**
	 * \ArrayIterator interface method.
	 *
	 * @return boolean
	 */
	public function current()
	{
		return $this->items[$this->current];
	}

	/**
	 * \ArrayIterator interface method.
	 *
	 * @return boolean
	 */
	public function key()
	{
		return $this->current;
	}

	/**
	 * \ArrayIterator interface method.
	 *
	 * @return boolean
	 */
	public function next()
	{
		++$this->current;
		return $this->items[$this->current];
	}

	/**
	 * \ArrayIterator interface method.
	 *
	 * @return boolean
	 */
	public function rewind()
	{
		$this->current = 0;
		return $this->items[$this->current];
	}

	/**
	 * \ArrayIterator interface method.
	 *
	 * @return boolean
	 */
	public function valid()
	{
		return isset($this->items[$this->current]);
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
	 * @throws \Jyxo\Gettext\Parser\Exception Non-existing method
	 */
	public function __call($name, $args)
	{
		if (substr($name, 0, 3) == 'get' && $var = substr($name, 3)) {
			$var = strtolower(substr($var, 0, 1)) . substr($var, 1);
			if (!isset($this->$var)) {
				throw new Parser\Exception(sprintf('Non-existing method %s::%s() called in %s, line %s', __CLASS__, $name, __FILE__, __LINE__));
			}
			return $this->$var;
		} elseif (substr($name, 0, 3) == 'set' && $var = substr($name, 3)) {
			$var = strtolower(substr($var, 0, 1)) . substr($var, 1);
			if (!isset($this->$var)) {
				throw new Parser\Exception(sprintf('Non-existing method %s::%s() called in %s, line %s', __CLASS__, $name, __FILE__, __LINE__));
			}

			$this->$var = $args[0];
			return $this;
		}
	}
}
