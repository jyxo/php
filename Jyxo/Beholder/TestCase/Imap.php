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

namespace Jyxo\Beholder\TestCase;

/**
 * Tests IMAP server availability.
 *
 * @category Jyxo
 * @package Jyxo\Beholder
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Imap extends \Jyxo\Beholder\TestCase
{
	/**
	 * Host.
	 *
	 * @var string
	 */
	private $host;

	/**
	 * Username.
	 *
	 * @var string
	 */
	private $user;

	/**
	 * Password.
	 *
	 * @var string
	 */
	private $password;

	/**
	 * Port.
	 *
	 * @var integer
	 */
	private $port;

	/**
	 * Validate certificates.
	 *
	 * @var boolean
	 */
	private $validateCert;

	/**
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param string $host Server hostname
	 * @param string $user Username
	 * @param string $password Password
	 * @param integer $port Port
	 * @param boolean $validateCert Validate certificates
	 */
	public function __construct($description, $host = 'localhost', $user = '', $password = '', $port = 143, $validateCert = true)
	{
		parent::__construct($description);

		$this->host = (string) $host;
		$this->user = (string) $user;
		$this->password = (string) $password;
		$this->port = (int) $port;
		$this->validateCert = (bool) $validateCert;
	}

	/**
	 * Performs the test.
	 *
	 * @return \Jyxo\Beholder\Result
	 */
	public function run()
	{
		// The imap extension is required
		if (!extension_loaded('imap')) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::NOT_APPLICABLE, 'Extension imap missing');
		}

		// Label for status
		$description = sprintf('%s@%s:%s', $this->user, $this->host, $this->port);

		$imap = imap_open('{' . $this->host . ':' . $this->port . '/' . (!$this->validateCert ? 'no' : '') . 'validate-cert}', $this->user, $this->password, OP_HALFOPEN, 1);
		if (false === $imap) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Connection error %s', $description));
		}
		imap_close($imap);

		return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::SUCCESS, $description);
	}
}
