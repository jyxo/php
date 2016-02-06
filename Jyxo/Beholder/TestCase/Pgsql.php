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

/**
 * Tests PostgreSQL availability.
 *
 * @category Jyxo
 * @package Jyxo\Beholder
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Pgsql extends \Jyxo\Beholder\TestCase
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
	 * @var integer
	 */
	private $port;

	/**
	 * Timeout.
	 *
	 * @var integer
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
	 * @param integer $port Port
	 * @param integer $timeout Timeout
	 */
	public function __construct(string $description, string $query, string $database, string $host = 'localhost', string $user = '', string $password = '', int $port = 5432, int $timeout = 2)
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
	 * @return \Jyxo\Beholder\Result
	 */
	public function run(): \Jyxo\Beholder\Result
	{
		// The pgsql extension is required
		if (!extension_loaded('pgsql')) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::NOT_APPLICABLE, 'Extension pgsql missing');
		}

		// Status label
		$description = sprintf('%s@%s:%s/%s', $this->user, $this->host, $this->port, $this->database);

		// Connection
		$db = pg_connect(sprintf(
			'host=%s port=%d dbname=%s user=%s password=%s connect_timeout=%d',
			$this->host, $this->port, $this->database, $this->user, $this->password, $this->timeout
		));
		if (false === $db) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Connection error %s', $description));
		}

		// Query (the leading space is because of pgpool)
		$result = pg_query($db, ' ' . $this->query);
		if (false === $result) {
			pg_close($db);
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Query error %s', $description));
		}

		pg_free_result($result);
		pg_close($db);

		// OK
		return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::SUCCESS, $description);
	}
}
