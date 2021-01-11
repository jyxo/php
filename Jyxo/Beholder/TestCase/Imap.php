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

namespace Jyxo\Beholder\TestCase;

use Jyxo\Beholder\Result;
use Jyxo\Beholder\TestCase;
use function extension_loaded;
use function imap_close;
use function imap_open;
use function sprintf;
use const OP_HALFOPEN;

/**
 * Tests IMAP server availability.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Imap extends TestCase
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
	 * @var int
	 */
	private $port;

	/**
	 * Validate certificates.
	 *
	 * @var bool
	 */
	private $validateCert;

	/**
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param string $host Server hostname
	 * @param string $user Username
	 * @param string $password Password
	 * @param int $port Port
	 * @param bool $validateCert Validate certificates
	 */
	public function __construct(
		string $description,
		string $host = 'localhost',
		string $user = '',
		string $password = '',
		int $port = 143,
		bool $validateCert = true
	)
	{
		parent::__construct($description);

		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->port = $port;
		$this->validateCert = $validateCert;
	}

	/**
	 * Performs the test.
	 *
	 * @return Result
	 */
	public function run(): Result
	{
		// The imap extension is required
		if (!extension_loaded('imap')) {
			return new Result(Result::NOT_APPLICABLE, 'Extension imap missing');
		}

		// Label for status
		$description = sprintf('%s@%s:%s', $this->user, $this->host, $this->port);

		$imap = imap_open(
			'{' . $this->host . ':' . $this->port . '/' . (!$this->validateCert ? 'no' : '') . 'validate-cert}',
			$this->user,
			$this->password,
			OP_HALFOPEN,
			1
		);

		if ($imap === false) {
			return new Result(Result::FAILURE, sprintf('Connection error %s', $description));
		}

		imap_close($imap);

		return new Result(Result::SUCCESS, $description);
	}

}
