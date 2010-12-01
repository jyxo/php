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

namespace Jyxo\Spl;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * Test pro třídu \Jyxo\Spl\ObjectCache.
 *
 * @see \Jyxo\Spl\ObjectCache
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček <libs@jyxo.com>
 */
class ObjectCacheTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Klíč nacachovaného objektu
	 *
	 * @var string
	 */
	const CACHE_KEY = 'myobject';

	/**
	 * Instance cache.
	 *
	 * @var \Jyxo\Spl\ObjectCache
	 */
	private $cache = null;


	/**
	 * Nastaví prostředí pro testy.
	 */
	protected function setUp()
	{
		$this->cache = \Jyxo\Spl\ObjectCache::getInstance();
	}

	/**
	 * Vyčistí prostředí po testech.
	 */
	protected function tearDown()
	{
		$this->cache = null;
	}

	/**
	 * Načtení objektu, když tam nic není
	 */
	public function testGetNull()
	{
		$this->assertNull($this->cache->{self::CACHE_KEY});
		$this->assertNull(\Jyxo\Spl\ObjectCache::get(self::CACHE_KEY));
	}

	/**
	 * Načtení dat
	 */
	public function testGetData()
	{
		$object = $this->saveObject();

		$this->assertSame($object, $this->cache->get(self::CACHE_KEY));
		$this->assertSame($this->cache->get(self::CACHE_KEY), $this->cache->{self::CACHE_KEY});

		$this->cache->clear();
	}

	/**
	 * Ukládání
	 */
	public function testSaveData()
	{
		$object = $this->saveObject();
		$this->assertSame($object, $this->cache->get(self::CACHE_KEY));

		// Nastavení téhož jedním způsobem
		\Jyxo\Spl\ObjectCache::set(self::CACHE_KEY, $object);
		$this->assertSame($object, $this->cache->get(self::CACHE_KEY));

		// Nastavení téhož druhým způsobem
		$this->cache->{self::CACHE_KEY} = $object;
		$this->assertSame($object, $this->cache->get(self::CACHE_KEY));

		$this->cache->clear();
	}

	/**
	 * Test Issetu
	 */
	public function testIsset()
	{
		// Nic není uloženo
		$this->assertFalse(isset($this->cache->{self::CACHE_KEY}));

		// Uložíme a zkontroluje
		$this->saveObject();
		$this->assertTrue(isset($this->cache->{self::CACHE_KEY}));
	}

	/**
	 * Uloží objekt do cache
	 *
	 * @return \stdClass
	 */
	private function saveObject()
	{
		$object = new \stdClass();
		$object->question = 'The Answer to the Ultimate Question of Life, the Universe, and Everything.';
		$object->answer = 42;
		$this->cache->{self::CACHE_KEY} = $object;

		return $object;
	}
}
