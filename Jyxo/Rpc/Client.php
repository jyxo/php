<?php

/**
 * Jyxo Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/jyxo/php/blob/master/license.txt
 */

namespace Jyxo\Rpc;

/**
 * Abstraktní třída pro odesílání požadavků přes RPC.
 *
 * @category Jyxo
 * @package Jyxo\Rpc
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
abstract class Client
{
	/**
	 * Url serveru.
	 *
	 * @var string
	 */
	protected $url = '';

	/**
	 * Časový limit na komunikaci s RPC serverem.
	 *
	 * @var integer
	 */
	protected $timeout = 5;

	/**
	 * Parametry pro vytvoření RPC požadavku.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Čas začátku požadavku.
	 *
	 * @var float
	 */
	private $time = 0;

	/**
	 * Zda profilovat požadavky.
	 *
	 * @var boolean
	 */
	private $profiler = false;

	/**
	 * Vytvoří instanci klienta a případně nastaví adresu serveru.
	 *
	 * @param string $url
	 */
	public function __construct($url = '')
	{
		if (!empty($url)) {
			$this->setUrl($url);
		}
	}

	/**
	 * Nastaví url.
	 *
	 * @param string $url
	 * @return \Jyxo\Rpc\Client
	 */
	public function setUrl($url)
	{
		$this->url = (string) $url;

		return $this;
	}

	/**
	 * Nastaví časový limit.
	 *
	 * @param integer $timeout
	 * @return \Jyxo\Rpc\Client
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = (int) $timeout;

		return $this;
	}

	/**
	 * Změní nastavení klienta.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return \Jyxo\Rpc\Client
	 */
	public function setOption($key, $value)
	{
		if (isset($this->options[$key])) {
			$this->options[$key] = $value;
		}
		return $this;
	}

	/**
	 * Vrátí určitý parametr nastavení, nebo celé pole, pokud není parametr zadán.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getOption($key = '')
	{
		if (isset($this->options[$key])) {
			return $this->options[$key];
		}
		return $this->options;
	}

	/**
	 * Zapne profilování.
	 *
	 * @return \Jyxo\Rpc\Client
	 */
	public function enableProfiler()
	{
		$this->profiler = true;
		return $this;
	}

	/**
	 * Vypne profilování.
	 *
	 * @return \Jyxo\Rpc\Client
	 */
	public function disableProfiler()
	{
		$this->profiler = false;
		return $this;
	}

	/**
	 * Odešle požadavek a získá ze serveru odpověď.
	 *
	 * @param string $method
	 * @param array $params
	 * @return mixed
	 * @throws \BadMethodCallException Pokud nebyla zadána url serveru
	 * @throws \Jyxo\Rpc\Exception Při chybě
	 */
	abstract public function send($method, array $params);

	/**
	 * Zpracuje data požadavku a získá odpověď.
	 *
	 * @param string $contentType
	 * @param string $data
	 * @return string
	 * @throws \BadMethodCallException Pokud nebyla zadána url serveru
	 * @throws \Jyxo\Rpc\Exception Při chybě
	 */
	protected function process($contentType, $data)
	{
		// Url musí být zadaná
		if (empty($this->url)) {
			throw new \BadMethodCallException('Nebyla zadána url serveru.');
		}

		// Hlavičky
		$headers = array(
			'Content-Type: ' . $contentType,
			'Content-Length: ' . strlen($data)
		);

		// Otevřeme HTTP kanál
		$channel = curl_init();
		curl_setopt($channel, CURLOPT_URL, $this->url);
		curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($channel, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($channel, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($channel, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($channel, CURLOPT_POSTFIELDS, $data);

		// Pošleme požadavek
		$response = curl_exec($channel);

		// Chyba v kanálu
		if (0 !== curl_errno($channel)) {
			$error = curl_error($channel);
			curl_close($channel);

			throw new \Jyxo\Rpc\Exception($error);
		}

		// Nesprávný kód
		$code = curl_getinfo($channel, CURLINFO_HTTP_CODE);
		if ($code >= 300) {
			$error = sprintf('Chyba odpovědi z %s, kód %d.', $this->url, $code);
			curl_close($channel);

			throw new \Jyxo\Rpc\Exception($error);
		}

		// Zavřeme kanál
		curl_close($channel);

		// Vrátíme získanou odpověď
		return $response;
	}

	/**
	 * Provede se na začátku profilování.
	 *
	 * @return \Jyxo\Rpc\Client
	 */
	protected function profileStart()
	{
		// Změříme dobu trvání požadavku
		$this->time = microtime(true);

		return $this;
	}

	/**
	 * Provede se na konci profilování.
	 *
	 * @param string $type
	 * @param string $method
	 * @param array $params
	 * @param mixed $response
	 * @return \Jyxo\Rpc\Client
	 */
	protected function profileEnd($type, $method, array $params, $response)
	{
		// Profilování musí být zapnuté
		if ($this->profiler) {
			static $totalTime = 0;
			static $requests = array();

			// Čas požadavku
			$time = microtime(true) - $this->time;

			$totalTime += $time;
			$requests[] = array(strtoupper($type), (string) $method, $params, $response, sprintf('%0.3f', $time * 1000));

			// Přidá do FirePHP
			\Jyxo\FirePhp::table(
				sprintf('Jyxo RPC Profiler (%d requests took %0.3f ms)', count($requests), sprintf('%0.3f', $totalTime * 1000)),
				array('Type', 'Method', 'Request', 'Response', 'Time'),
				$requests,
				'Rpc'
			);
		}

		return $this;
	}
}
