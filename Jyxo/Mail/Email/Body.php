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

namespace Jyxo\Mail\Email;

/**
 * Email body.
 *
 * @category Jyxo
 * @package Jyxo\Mail
 * @subpackage Email
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Body extends \Jyxo\Spl\Object
{
	/**
	 * Main body contents.
	 *
	 * @var string
	 */
	private $main = '';

	/**
	 * Alternative body contents.
	 *
	 * @var string
	 */
	private $alternative = '';

	/**
	 * Creates email body.
	 *
	 * @param string $main Main contents
	 * @param string $alternative Alternative contents
	 */
	public function __construct(string $main, string $alternative = '')
	{
		$this->setMain($main);
		$this->setAlternative($alternative);
	}

	/**
	 * Returns if the contents is in HTML format.
	 *
	 * @return boolean
	 */
	public function isHtml(): bool
	{
		return \Jyxo\Html::is($this->main);
	}

	/**
	 * Returns main body contents.
	 *
	 * @return string
	 */
	public function getMain(): string
	{
		return $this->main;
	}

	/**
	 * Sets main body contents.
	 *
	 * @param string $main Contents
	 * @return \Jyxo\Mail\Email\Body
	 */
	public function setMain(string $main): self
	{
		$this->main = $main;

		return $this;
	}

	/**
	 * Returns alternative body contents.
	 *
	 * @return string
	 */
	public function getAlternative(): string
	{
		return $this->alternative;
	}

	/**
	 * Sets alternative body contents.
	 *
	 * @param string $alternative Contents
	 * @return \Jyxo\Mail\Email\Body
	 */
	public function setAlternative(string $alternative): self
	{
		$this->alternative = $alternative;

		return $this;
	}
}
