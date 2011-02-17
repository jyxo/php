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
 * Walks through the given text and computes individual words counts. If more than 3/4 words repeat
 * the text is considered to be spam.
 *
 * @category Jyxo
 * @package Jyxo\SpamFilter
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Roman Řáha
 */
class SpamFilter
{
	/**
	 * Maximal number of links in the text.
	 *
	 * @var integer
	 */
	const LINK_MAX_COUNT = 10;

	/**
	 * Maximal number of links in a short text.
	 *
	 * @var integer
	 */
	const LINK_SHORT_MAX_COUNT = 3;

	/**
	 * Ratio of links number to the total words number in the text.
	 *
	 * @var float
	 */
	const LINK_MAX_RATIO = 0.05;

	/**
	 * Minimal words count where the links ratio is computed.
	 *
	 * @var integer
	 */
	const LINK_WORDS_MIN_COUNT = 30;

	/**
	 * Words blacklist.
	 *
	 * @var array
	 */
	private $blackList = array();

	/**
	 * Ignored words (words are array keys).
	 *
	 * @var array
	 */
	private $ignoreWords = array();

	/**
	 * Checks if the given text is spam.
	 *
	 * @param string $text Checked text
	 * @return boolean
	 */
	public function isSpam($text)
	{
		// Blacklisting first
		if ($this->isBlack($text)) {
			return true;
		}
		// Link count check
		if ($this->isLinkSpam($text)) {
			return true;
		}
		// Words repeat check
		if ($this->isBabble($text)) {
			return true;
		}
		return false;
	}

	/**
	 * Returns if the given text contains blacklisted words.
	 *
	 * @param string $text Checked text
	 * @return boolean
	 */
	public function isBlack($text)
	{
		foreach ($this->blackList as $black) {
			if (false !== strpos($text, $black)) {
				// There is a blacklisted word in the text
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns if the text contains too many links.
	 *
	 * @param string $text Checked text
	 * @return boolean
	 */
	public function isLinkSpam($text)
	{
		$urlPattern = '~((ftp|http|https)://)?[-\w]+(\.[-\w]+)*\.[a-z]{2,6}~i';
		$linkCount = preg_match_all($urlPattern, $text, $matches);
		if (self::LINK_MAX_COUNT <= $linkCount) {
			// More links than allowed
			return true;
		}
		$wordCount = preg_match_all('~[\pZ\s]+~u', trim($text), $matches) + 1;
		if (self::LINK_WORDS_MIN_COUNT >= $wordCount) {
			// For short texts use links count check
			return self::LINK_SHORT_MAX_COUNT <= $linkCount;
		}
		// For long texts check links ratio
		return self::LINK_MAX_RATIO <= ($linkCount / $wordCount);
	}

	/**
	 * Returns if the text consists of repeating parts.
	 * Returns true if the number of at least three times repeated words is greater than
	 * 3/4 of all words.
	 *
	 * @param string $text Checked text
	 * @return boolean
	 */
	public function isBabble($text)
	{
		$words = array();
		$numberOfWords = 0;
		// Walk through the text a count word appearances
		foreach (preg_split('~[\pZ\s]+~u', $text) as $word) {
			$word = mb_strtolower(trim($word), 'utf-8');
			// Check if the word is supposed to be ignored
			if (!isset($this->ignoreWords[$word])) {
				if (isset($words[$word])) {
					$words[$word]++;
				} else {
					$words[$word] = 1;
				}
				$numberOfWords++;
			}
		}

		// Count words repeated more than two times
		$count = 0;
		foreach ($words as $value) {
			if ($value > 2) {
				$count += $value;
			}
		}

		// If the number of repeated words is greater than 3/4 of all words, the text is considered to be spam
		return $count > ($numberOfWords * 3 / 4);
	}

	/**
	 * Sets word blacklist.
	 *
	 * @param array $blackList Words blacklist
	 * @return \Jyxo\SpamFilter
	 */
	public function setBlackList(array $blackList)
	{
		$this->blackList = $blackList;
		return $this;
	}

	/**
	 * Sets ignored word list.
	 *
	 * @param array $ignoreWords Ignored words list
	 * @return \Jyxo\SpamFilter
	 */
	public function setIgnoreWords(array $ignoreWords)
	{
		$this->ignoreWords = $ignoreWords;
		return $this;
	}
}
