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

namespace Jyxo\Gettext\Parser;

use function explode;
use function preg_match;

/**
 * Container class for translation properties.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Matěj Humpál
 */
class Item
{

	/**
	 * Msgid.
	 *
	 * @var string
	 */
	protected $msgid = '';

	/**
	 * Msgid translations.
	 *
	 * For each plural form one array field.
	 * If there is only one translation (no plural forms), it is stored as a string.
	 *
	 * @var string|array
	 */
	protected $msgstr = '';

	/**
	 * Plural forms.
	 *
	 * @var string
	 */
	protected $plural = '';

	/**
	 * Msgid position in the source code.
	 *
	 * @var string
	 */
	protected $location = '';

	/**
	 * Is msgid fuzzy?
	 *
	 * @var bool
	 */
	protected $fuzzy = false;

	/**
	 * Is msgid obsolete?
	 *
	 * @var bool
	 */
	protected $obsolete = false;

	/**
	 * Constructor.
	 *
	 * Retrieves a fragment of the PO file and parses it.
	 *
	 * @param string $chunk Translation fragment
	 */
	public function __construct(string $chunk)
	{
		$array = explode("\n", $chunk);
		$this->parse($array);

		if (empty($this->msgid)) {
			throw new Exception('Msgid is empty which is not acceptable');
		}
	}

	/**
	 * Returns whether the msgid is fuzzy.
	 *
	 * @return bool
	 */
	public function isFuzzy(): bool
	{
		return $this->fuzzy;
	}

	/**
	 * Returns whether the msgid is obsolete.
	 *
	 * @return bool
	 */
	public function isObsolete(): bool
	{
		return $this->obsolete;
	}

	/**
	 * Returns whether the msgid has plural forms.
	 *
	 * @return bool
	 */
	public function hasPlural(): bool
	{
		return !empty($this->plural);
	}

	/**
	 * Returns msgid's position in source codes.
	 *
	 * @return string|array
	 */
	public function getLocation()
	{
		return $this->location;
	}

	/**
	 * Returns msgid.
	 *
	 * @return string|array
	 */
	public function getMsgid()
	{
		return $this->msgid;
	}

	/**
	 * Returns msgstr.
	 *
	 * @return string|array
	 */
	public function getMsgstr()
	{
		return $this->msgstr;
	}

	/**
	 * Returns plural translations.
	 *
	 * @return string
	 */
	public function getPlural(): string
	{
		return $this->plural;
	}

	/**
	 * The actual parser.
	 *
	 * @param array $chunks Lines of the PO file fragment
	 */
	protected function parse(array $chunks): void
	{
		foreach ($chunks as $chunk) {
			if (preg_match('/^"(.*)"/', $chunk, $matches)) {
				$this->{$lastChunkType} .= $matches[1];

				continue;
			}

			if (preg_match('/^msgid "(.*)"/', $chunk, $matches)) {
				$lastChunkType = 'msgid';
				$this->msgid = $matches[1];
			} elseif (preg_match('/^msgstr "(.*)"/', $chunk, $matches)) {
				$lastChunkType = 'msgstr';
				$this->msgstr = $matches[1];
			} elseif (preg_match('/^#~ msgid "(.*)"/', $chunk, $matches)) {
				$lastChunkType = 'msgid';
				$this->obsolete = true;
				$this->msgid = $matches[1];
			} elseif (preg_match('/^#~ msgstr "(.*)"/', $chunk, $matches)) {
				$lastChunkType = 'msgstr';
				$this->obsolete = true;
				$this->msgstr = $matches[1];
			} elseif (preg_match('/^(#: .+)$/', $chunk, $matches)) {
				$lastChunkType = 'location';
				$this->location .= $matches[1];
			} elseif (preg_match('/^#, fuzzy/', $chunk)) {
				$lastChunkType = 'fuzzy';
				$this->fuzzy = true;
			} elseif (preg_match('/^msgid_plural "(.*)"/', $chunk, $matches)) {
				$lastChunkType = 'plural';
				$this->plural = $matches[1];
				$this->msgstr = [];
			} elseif (preg_match('/^msgstr\[([0-9])+\] "(.*)"/', $chunk, $matches)) {
				$lastChunkType = 'msgstr';
				$this->msgstr[$matches[1]] = $matches[2];
			}
		}
	}

}
