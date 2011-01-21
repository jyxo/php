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

namespace Jyxo;

/**
 * XMLReader child class (available since PHP 5.1).
 *
 * @category Jyxo
 * @package Jyxo\XmlReader
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class XmlReader extends \XMLReader
{
	/**
	 * Text types list.
	 *
	 * @var array
	 */
	private $textTypes = array(
		self::TEXT => true,
		self::WHITESPACE => true,
		self::SIGNIFICANT_WHITESPACE => true
	);

	/**
	 * Content types list.
	 *
	 * @var array
	 */
	private $contentTypes = array(
		self::CDATA => true,
		self::TEXT => true,
		self::WHITESPACE => true,
		self::SIGNIFICANT_WHITESPACE => true
	);

	/**
	 * Returns element's text contents.
	 *
	 * @return string
	 */
	public function getTextValue()
	{
		$depth = 1;
		$text = '';

		while ((0 !== $depth) && ($this->read())) {
			if (isset($this->textTypes[$this->nodeType])) {
				$text .= $this->value;
			} elseif (self::ELEMENT === $this->nodeType) {
				if (!$this->isEmptyElement) {
					$depth++;
				}
			} elseif (self::END_ELEMENT === $this->nodeType) {
				$depth--;
			}
		}
		return $text;
	}

	/**
	 * Returns element's contents including tags.
	 *
	 * @return string
	 */
	public function getContent()
	{
		$depth = 1;
		$text = '';

		while ((0 !== $depth) && ($this->read())) {
			if (isset($this->contentTypes[$this->nodeType])) {
				$text .= $this->value;
			} elseif (self::ELEMENT === $this->nodeType) {
				if ($this->isEmptyElement) {
					$text .= '<' . $this->name . '/>';
				} else {
					$depth++;
					$text .= '<' . $this->name;

					while ($this->moveToNextAttribute()) {
						$text .= ' ' . $this->name . '="' . $this->value . '"';
					}

					$text .= '>';
				}
			} elseif (self::END_ELEMENT === $this->nodeType) {
				$depth--;
				if ($depth > 0) {
					$text .= '</' . $this->name . '>';
				}
			}
		}

		return $text;
	}
}
