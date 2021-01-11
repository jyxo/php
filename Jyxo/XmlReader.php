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

namespace Jyxo;

/**
 * XMLReader child class (available since PHP 5.1).
 *
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
	private $textTypes = [
		self::TEXT => true,
		self::WHITESPACE => true,
		self::SIGNIFICANT_WHITESPACE => true,
	];

	/**
	 * Content types list.
	 *
	 * @var array
	 */
	private $contentTypes = [
		self::CDATA => true,
		self::TEXT => true,
		self::WHITESPACE => true,
		self::SIGNIFICANT_WHITESPACE => true,
	];

	/**
	 * Returns element's text contents.
	 *
	 * @return string
	 */
	public function getTextValue(): string
	{
		if ($this->nodeType === self::ELEMENT && $this->isEmptyElement) {
			return '';
		}

		$depth = 1;
		$text = '';

		while (($depth !== 0) && ($this->read())) {
			if (isset($this->textTypes[$this->nodeType])) {
				$text .= $this->value;
			} elseif ($this->nodeType === self::ELEMENT) {
				if (!$this->isEmptyElement) {
					$depth++;
				}
			} elseif ($this->nodeType === self::END_ELEMENT) {
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
	public function getContent(): string
	{
		if ($this->nodeType === self::ELEMENT && $this->isEmptyElement) {
			return '';
		}

		$depth = 1;
		$text = '';

		while (($depth !== 0) && ($this->read())) {
			if (isset($this->contentTypes[$this->nodeType])) {
				$text .= $this->value;
			} elseif ($this->nodeType === self::ELEMENT) {
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
			} elseif ($this->nodeType === self::END_ELEMENT) {
				$depth--;

				if ($depth > 0) {
					$text .= '</' . $this->name . '>';
				}
			}
		}

		return $text;
	}

}
