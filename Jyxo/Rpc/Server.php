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
 * Třída pro vytvoření RPC serveru.
 *
 * @category Jyxo
 * @package Jyxo\Rpc
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
abstract class Server
{
	/**
	 * Aliasy skutečných funkcí.
	 *
	 * @var array
	 */
	private $aliases = array();

	/**
	 * Soubor, do kterého se loguje provoz.
	 *
	 * @var string
	 */
	private $logFile;

	/**
	 * Funkce, která se zavolá před logováním.
	 * Lze s ní zajistit, aby se některá data (např. hesla) nezalogovala.
	 *
	 * @var callback
	 */
	private $logCallback;

	/**
	 * Vytvoří instanci třídy.
	 */
	protected function __construct()
	{}

	/**
	 * Zruší instanci třídy.
	 */
	public function __destruct()
	{}

	/**
	 * Zakáže klonování u singletonu.
	 *
	 * @throws \LogicException Při pokusu o klonování
	 */
	public final function __clone()
	{
		throw new \LogicException(sprintf('Objekt třídy %s může mít pouze jednu instanci.', get_class($this)));
	}

	/**
	 * Vrátí instanci serveru.
	 *
	 * @return \Jyxo\Rpc\Server
	 */
	public static function getInstance()
	{
		static $instance;
		if (null === $instance) {
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * Zapne logování provozu.
	 *
	 * @param string $filename Cesta k souboru.
	 * @param callback $callback Funkce, která se zavolá před logováním.
	 * @return \Jyxo\Rpc\Server
	 * @throws \InvalidArgumentException Pokud nebyl soubor zadán nebo byla zadána nevolatelná funkce.
	 */
	public function enableLogging($filename, $callback = null)
	{
		$filename = (string) $filename;
		$filename = trim($filename);

		// Kontrola souboru
		if (empty($filename)) {
			throw new \InvalidArgumentException('Nebyl zadán soubor pro logování.');
		}

		$this->logFile = $filename;

		// Funkce musí být volatelná
		if ((!empty($callback)) && (!is_callable($callback))) {
			throw new \InvalidArgumentException('Funkce není volatelná.');
		}

		$this->logCallback = $callback;

		return $this;
	}

	/**
	 * Zaregistruje veřejné metody zadané třídy.
	 *
	 * @param string $class
	 * @param boolean $useFullName Zda zaregistrovat plné jméno i s názvem třídy
	 * @return \Jyxo\Rpc\Server
	 * @throws \InvalidArgumentException Pokud třída neexistuje
	 */
	public function registerClass($class, $useFullName = true)
	{
		if (!class_exists($class)) {
			throw new \InvalidArgumentException(sprintf('Třída %s neexistuje.', $class));
		}

		$reflection = new \ReflectionClass($class);
		foreach ($reflection->getMethods() as $method) {
			// Pouze veřejné metody
			if ($method->isPublic()) {
				$func = $class . '::' . $method->getName();

				// Uloží zkrácené jméno jako alias
				if (!$useFullName) {
					$this->aliases[$method->getName()] = $func;
					$func = $method->getName();
				}

				$this->register($func);
			}
		}

		return $this;
	}

	/**
	 * Zaregistruje zadanou metodu zadané třídy.
	 * Metoda nemusí nutně existovat, stačí pokud existuje jedna z metod __call a __callStatic.
	 *
	 * @param string $class
	 * @param string $method
	 * @param boolean $useFullName Zda zaregistrovat plné jméno i s názvem třídy
	 * @return \Jyxo\Rpc\Server
	 * @throws \InvalidArgumentException Pokud třída nebo metoda neexistuje, nebo metoda není veřejná.
	 */
	public function registerMethod($class, $method, $useFullName = true)
	{
		if (!class_exists($class)) {
			throw new \InvalidArgumentException(sprintf('Třída %s neexistuje.', $class));
		}

		// Pokud existují magické metody, tak registrujeme vždy
		if ((!method_exists($class, '__call')) && (!method_exists($class, '__callStatic'))) {
			try {
				$reflection = new \ReflectionMethod($class, $method);
			} catch (\ReflectionException $e) {
				throw new \InvalidArgumentException(sprintf('Metoda %s::%s neexistuje.', $class, $method));
			}

			// Pouze veřejné metody
			if (!$reflection->isPublic()) {
				throw new \InvalidArgumentException(sprintf('Metoda %s::%s není veřejná.', $class, $method));
			}
		}

		$func = $class . '::' . $method;

		// Uloží zkrácené jméno jako alias
		if (!$useFullName) {
			$this->aliases[$method] = $func;
			$func = $method;
		}

		$this->register($func);

		return $this;
	}

	/**
	 * Zaregistruje zadanou funkci.
	 *
	 * @param string $func
	 * @return \Jyxo\Rpc\Server
	 * @throws \InvalidArgumentException Pokud funkce neexistuje
	 */
	public function registerFunc($func)
	{
		if (!function_exists($func)) {
			throw new \InvalidArgumentException(sprintf('Funkce %s neexistuje.', $func));
		}

		$this->register($func);

		return $this;
	}

	/**
	 * Skutečně zaregistruje funkci.
	 *
	 * @param string $func
	 */
	abstract protected function register($func);

	/**
	 * Zpracuje požadavek a odešle RPC odpověď.
	 */
	abstract public function process();

	/**
	 * Zajistí zavolání zaregistrované metody s danými parametry.
	 *
	 * @param string $method
	 * @param array $params
	 * @return mixed
	 */
	protected function call($method, $params)
	{
		$func = $method;
		// Pokud je funkce pouze alias ke skutečné metodě, tak se použije skutečná metoda
		if (isset($this->aliases[$method])) {
			$func = $this->aliases[$method];
		}

		// Metoda třídy
		if (false !== strpos($func, '::')) {
			list($className, $methodName) = explode('::', $func);

			try {
				// Metoda existuje
				$reflection = new \ReflectionMethod($className, $methodName);
				if ($reflection->isStatic()) {
					// Metoda je statická
					$callback = array($className, $methodName);
				} else {
					// Metoda není statická
					$callback = array(new $className(), $methodName);
				}
			} catch (\ReflectionException $e) {
				// Metoda neexistuje
				if (method_exists($className, '__call')) {
					// Je dostupné __call
					$callback = array(new $className(), $methodName);
				} else {
					// Je dostupné __callStatic
					$callback = array($className, $methodName);
				}
			}
		} else {
			// Jednoduchá funkce
			$callback = $func;
		}

		$result = call_user_func_array($callback, $params);

		// Logování
		$this->log($method, $params, $result);

		return $result;
	}

	/**
	 * Zaloguje požadavek.
	 *
	 * @param string $method Metoda
	 * @param array $params Parametry
	 * @param mixed $result Výsledek
	 */
	private function log($method, $params, $result)
	{
		// Logujeme, pokud je zadán soubor
		if (!empty($this->logFile)) {
			// Pokud je zadaná funkce, pročistí se data
			if (!empty($this->logCallback)) {
				list($method, $params, $result) = call_user_func($this->logCallback, $method, $params, $result);
			}

			// Metoda
			$text = sprintf("Method: %s\n", $method);
			// Parametry
			foreach ($params as $paramName => $param) {
				$text .= sprintf("Param %s: %s\n", $paramName, trim(print_r($param, true)));
			}
			// Výsledek
			$text .= sprintf("Result: %s\n", trim(print_r($result, true)));

			// Pro přehlednost odsadíme
			$text = strtr(trim($text), array("\n" => "\n\t"));

			// Čas, ip, host a uri
			$text = sprintf("[%s] %s %s %s\n\t%s\n", date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'], $text);

			// Uložení
			file_put_contents($this->logFile, $text, FILE_APPEND);
		}
	}
}
