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
use function mysqli_close;
use function mysqli_free_result;
use function mysqli_init;
use function mysqli_options;
use function mysqli_query;
use function mysqli_real_connect;
use function sprintf;
use const MYSQLI_OPT_CONNECT_TIMEOUT;

/**
 * Tests MySQL availability.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Mysql extends TestCase
{

	/**
	 * SQL query.
	 *
	 * @var string
	 */
	private $query;

	/**
	 * Database name.
	 *
	 * @var string
	 */
	private $database;

	/**
	 * Hostname.
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
	 * Timeout.
	 *
	 * @var int
	 */
	private $timeout;

	/**
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param string $query Tested query
	 * @param string $database Database name
	 * @param string $host Hostname
	 * @param string $user Username
	 * @param string $password Password
	 * @param int $port Port
	 * @param int $timeout Timeout
	 */
	public function __construct(
		string $description,
		string $query,
		string $database,
		string $host = 'localhost',
		string $user = '',
		string $password = '',
		int $port = 3306,
		int $timeout = 2
	)
	{
		parent::__construct($description);

		$this->query = $query;
		$this->database = $database;
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->port = $port;
		$this->timeout = $timeout;
	}

	/**
	 * Performs the test.
	 *
	 * @return Result
	 */
	public function run(): Result
	{
		// The mysqli extension is required
		if (!extension_loaded('mysqli')) {
			return new Result(Result::NOT_APPLICABLE, 'Extension \mysqli missing');
		}

		// Status label
		$description = sprintf('%s@%s:%s/%s', $this->user, $this->host, $this->port, $this->database);

		// Connection
		$db = mysqli_init();

		if (!$db) {
			return new Result(Result::FAILURE, sprintf('Connection error %s', $description));
		}

		if (mysqli_options($db, MYSQLI_OPT_CONNECT_TIMEOUT, $this->timeout) === false) {
			return new Result(Result::FAILURE, sprintf('Connection error %s', $description));
		}

		if (mysqli_real_connect($db, $this->host, $this->user, $this->password, $this->database, $this->port) === false) {
			return new Result(Result::FAILURE, sprintf('Connection error %s', $description));
		}

		// Query
		$result = mysqli_query($db, $this->query);

		if ($result === false) {
			mysqli_close($db);

			return new Result(Result::FAILURE, sprintf('Query error %s', $description));
		}

		mysqli_free_result($result);
		mysqli_close($db);

		// OK
		return new Result(Result::SUCCESS, $description);
	}

}
