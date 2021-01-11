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

namespace Jyxo\Input;

use InvalidArgumentException;
use Jyxo\Input\Validator\Exception;
use Jyxo\Input\Validator\IsInt;
use Jyxo\Input\Validator\LessThan;
use Jyxo\Spl\ObjectCache;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;
use function array_merge;
use function array_reverse;
use function count;
use function date;
use function is_numeric;
use function print_r;
use function shuffle;
use function sprintf;

require_once __DIR__ . '/../../files/input/Validator.php';

/**
 * Tests of \Jyxo\Input package validators.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 * @author Jaroslav Hanslík
 * @author Ondřej Nešpor
 */
class ValidatorTest extends TestCase
{

	/**
	 * Validator error code.
	 */
	public const ERROR_CODE = 42;

	/**
	 * Validator error message.
	 */
	public const ERROR_MESSAGE = 'Error!';

	/**
	 * Tests Tests InArray validator.
	 */
	public function testInArray(): void
	{
		$values = [1, 2, '3', null, 'foo'];

		$good = [
			1,
			null,
			2,
			'foo',
		];
		$wrong = [
			'bah',
			4,
			1.5,
			'2',
			true,
			false,
			'',
		];

		$this->executeTests(new Validator\InArray($values), $good, $wrong);
	}

	/**
	 * Tests Tests IsArray validator.
	 */
	public function testIsArray(): void
	{
		$good = [
			['test'],
		];
		$wrong = [
			'test',
			123,
			1.5,
			true,
		];

		$this->executeTests(new Validator\IsArray(), $good, $wrong);
	}

	/**
	 * Tests IsBirthNumber validator.
	 */
	public function testIsBirthNumber(): void
	{
		$good = [
			'8203050218',
			'820305/0218',
			'820305 0218',
			'340621/026',
			'840501/1330',
			'0681186066',
			'0531135099',
			'345514/1360',
		];
		$wrong = [
			'820305 0219',
			'820332/0218',
			'820305-0218',
			'670229/1125',
			'670229/1145',
			'540621/026',
			10,
		];

		$this->executeTests(new Validator\IsBirthNumber(), $good, $wrong);
	}

	/**
	 * Tests IsCompanyId validator.
	 */
	public function testIsCompanyId(): void
	{
		$good = [
			'26704706',
			'27401944',
			'25596641',
			'14800381',
			'47782170',
		];
		$wrong = [
			'267047',
			'26704705',
			10,
		];

		$this->executeTests(new Validator\IsCompanyId(), $good, $wrong);
	}

	/**
	 * Tests IsCountryCode validator.
	 */
	public function testIsCountryCode(): void
	{
		$good = $this->getGoodValues(Validator\IsCountryCode::getCountries());
		$wrong = [
			'A',
			'B',
			1,
			0,
		];

		$this->executeTests(new Validator\IsCountryCode(), $good, $wrong);
	}

	/**
	 * Tests IsDate validator.
	 */
	public function testIsDate(): void
	{
		$good = [
			'1993-01-01',
			'2000-02-29',
			date('Y-m-d'),
		];
		$wrong = [
			23,
			'2009-02-29',
		];

		$this->executeTests(new Validator\IsDate(), $good, $wrong);
	}

	/**
	 * Tests IsDateTime validator.
	 */
	public function testIsDateTime(): void
	{
		$good = [
			'1993-01-01 01:00:00',
			'2000-02-29 15:23:59',
			date('Y-m-d H:i:s'),
		];
		$wrong = [
			23,
			'2009-02-29 01:00:00',
			'2009-02-28 24:00:00',
			'2009-02-28 23:60:00',
			'2009-02-28 23:59:60',
		];

		$this->executeTests(new Validator\IsDateTime(), $good, $wrong);
	}

	/**
	 * Tests IsEmail validator.
	 */
	public function testIsEmail(): void
	{
		$good = [
			'test@jyxo.com',
			'velky.ohromny.test@jyxo.blog.cz',
			'123@jyxo.com',
			'ZahradyR+R@email.cz',
			'muj@dobry.restaurant',
			'test@test.xn--vermgensberatung-pwb',
		];
		$wrong = [
			'česko@jyxo.com',
			'test test@jyxo.com',
			'test.jyxo.com',
			'test@test@jyxo.com',
			'test.@jyxo.com',
			'.test@jyxo.com',
			'test..test@jyxo.com',
		];

		$this->executeTests(new Validator\IsEmail(), $good, $wrong);
	}

	/**
	 * Tests IsInt validator.
	 */
	public function testIsInt(): void
	{
		$good = ['1', 1, '0', 0, '12345'];
		$wrong = ['0xFF', '+0123.45e6'];

		$this->executeTests(new Validator\IsInt(), $good, $wrong);
	}

	/**
	 * Tests IsIpV4 validator.
	 */
	public function testIsIpV4(): void
	{
		$good = [
			'127.0.0.1',
			'192.168.24.0',
			'217.31.54.133',
			'10.0.0.71',
			'212.47.13.165',
			'89.235.3.140',
			'147.32.127.214',
		];
		$wrong = ['999.999.999.999'];

		$this->executeTests(new Validator\IsIpV4(), $good, $wrong);
	}

	/**
	 * Tests IsIpV6 validator.
	 */
	public function testIsIpV6(): void
	{
		$good = [
			'2001:0db8:0000:0000:0000:0000:1428:57ab',
			'2001:0db8:0000:0000:0000::1428:57ab',
			'2001:0db8:0:0:0:0:1428:57ab',
			'2001:0db8:0:0::1428:57ab',
			'2001:0db8::1428:57ab',
			'2001:db8::1428:57ab',
			'2001:0db8:85a3:08d3:1319:8a2e:0370:7344',
			'2001:0718:1c01:0016:0214:22ff:fec9:0ca5',
			'2001:718:1c01:16:214:22ff:fec9:ca5',
			'ff02:0:0:0:0:0:0:1',
		];

		$wrong = [
			'xx02:db8::1428:57ab',
			'2001:0xx8:0:0:0:0:1428:57yy',
		];

		$this->executeTests(new Validator\IsIpV6(), $good, $wrong);
	}

	/**
	 * Tests IsNumeric validator.
	 */
	public function testIsNumeric(): void
	{
		$good = ['1', 1, '0', 0, '42', '1e4', 9.1, '9.1', '+0123.45e6', '-12.2e-6'];
		$wrong = ['not numeric', [], '123a4', '9,1', '0xBW'];

		$this->executeTests(new Validator\IsNumeric(), $good, $wrong);
	}

	/**
	 * Tests IsPhone validator.
	 */
	public function testIsPhone(): void
	{
		$good = [
			'112',
			'1188',
			'11233',
			'800123456',
			'800 02 02 02',
			'223456789',
			'+420223456789',
			'00420223456789',
			'223 456 789',
			'223 45 67 89',
		];
		$wrong = [
			'1',
			'22345678',
			'-420223456789',
			// 8-number must not have a pre-dial
			'+420800020202',
			'0420123456789',
		];

		$this->executeTests(new Validator\IsPhone(), $good, $wrong);
	}

	/**
	 * Tests IsTaxId validator.
	 */
	public function testIsTaxId(): void
	{
		$good = [
			'CZ 26704706',
			'CZ 8405011330',
		];
		$wrong = [
			'SK12345678',
			'CZ 8405011328',
			'267-26704706',
		];

		$this->executeTests(new Validator\IsTaxId(), $good, $wrong);

		// Try the so called "own numbers"
		$taxId = 'CZ12345678';
		$this->executeTests(new Validator\IsTaxId(false), [$taxId], []);
	}

	/**
	 * Tests IsUrl validator.
	 */
	public function testIsUrl(): void
	{
		$good = [
			'http://jyxo.com',
			'http://www.jyxo.com',
			'www.jyxo.cz',
			'jyxo.blog.cz',
			'http://www.google.cz/search?hl=cs&q=jyxo&btnG=Hledat&lr',
			'https://www.google.com/accounts/ServiceLogin?hl=en&continue=http://www.google.com/history/welcome%3Fhl%3Den%26zx%3DUZHUZwVdsQQ&nui=1&service=hist',
			'http://cybex.cz/Produkt.aspx?Shortcut=FPC-202167&Type=K&CategoryId=0',
			'http://www.dobryweb.cz/skoleni-google-adwords/?seo',
			'http://cs.wikipedia.org/wiki/%C5%98%C3%ADmsk%C3%A9_%C4%8D%C3%ADslice#Nula',
			'http://lidovky.zpravy.cz/hlasujte-co-je-nejosklivejsi-di6-/ln_redakce.asp?c=A070420_194521_ln_redakce_znk',
			'http://wiki.jyxo.com/index.php/Hlavn%C3%AD_strana',
			'http://meta.wikimedia.org/wiki/Logo#Proposing_new_logos',
			'http://firma.aspi.cz:5180/cgi/query?d=ai&ic=utf-8&oc=utf-8&timeout=30&s=%2A+%24source%3D6+%24product%3D2&cnt=5&sort=0&ctsize=200&o=stemhint',
			'http://feed.com.br/feex!2.xml',
			'http://www.example.com/',
			'http://user:password@example.com/test/index.html',
			'www.example.com/test/index.html',
			'http://example.com',
			'http://www.o.k.navy.cz',
			'http://www.youtube.com/v/I5ewQSfrn9Q&hl=cs&fs=1&',
			'http://www.youtube.com/watch#!v=7gcxnoA9K0k&feature=related',
			'http://image.tn.nova.cz/media/images///600x277/May2009//505002.jpg',
		];
		$wrong = [
			'http://www.džikso.cz/',
			'htt://www.jyxo.cz/',
			'http://www.jyxo.čz/',
			'http://a.cz',
			'http://www.a.cz',
			'http://domain._abc.com',
		];

		$this->executeTests(new Validator\IsUrl(), $good, $wrong);
	}

	/**
	 * Tests IsZipCode validator.
	 */
	public function testIsZipCode(): void
	{
		$good = [
			'14000',
			'140 00',
		];
		$wrong = [
			'1400a',
			'1400',
		];

		$this->executeTests(new Validator\IsZipCode(), $good, $wrong);
	}

	/**
	 * Tests LessThan validator.
	 */
	public function testLessThan(): void
	{
		$good = [
			0,
			10,
			-10,
			'-100',
		];
		$wrong = [
			101,
			'102',
		];

		$validator = new Validator\LessThan(100);
		$this->executeTests($validator, $good, $wrong);
		$this->assertEquals(100, $validator->getMax());
	}

	/**
	 * Tests Equals validator.
	 */
	public function testEquals(): void
	{
		$expected = 123;
		$good = [
			123,
		];
		$bad = [
			12,
			'123',
			'A123',
			true,
			false,
		];

		$validator = new Validator\Equals($expected);

		$this->executeTests($validator, $good, $bad);
		$this->assertSame($expected, $validator->getExpected());
	}

	/**
	 * Tests NotEmpty validator.
	 */
	public function testNotEmpty(): void
	{
		$good = [
			'NULL',
			[0],
		];
		$wrong = [
			'',
			0,
			'0',
			[],
			null,
		];

		$this->executeTests(new Validator\NotEmpty(), $good, $wrong);
	}

	/**
	 * Tests Regex validator.
	 */
	public function testRegex(): void
	{
		$good = [
			'test',
			'123',
			'JYXO',
		];
		$wrong = [
			'test-test',
			'$test',
			'--',
			'..//',
		];
		$pattern = '~^\w+$~i';

		$validator = new Validator\Regex($pattern);
		$this->executeTests($validator, $good, $wrong);
		$this->assertEquals($pattern, $validator->getPattern());

		$this->expectException(Exception::class);
		$validator->setPattern('');
	}

	/**
	 * Tests StringLengthGraterThan validator.
	 */
	public function testStringLengthGreaterThan(): void
	{
		$good = [
			'test-test-test',
			'ěščřžýáíéíáýž',
		];
		$wrong = [
			'žlutý',
			'test',
		];
		$length = 10;

		$validator = new Validator\StringLengthGreaterThan($length);
		$this->executeTests($validator, $good, $wrong);
		$this->assertEquals($length, $validator->getMin());

		try {
			$validator->setMin(-10);
			$this->fail(sprintf('Expected exception %s.', InvalidArgumentException::class));
		} catch (AssertionFailedError $e) {
			throw $e;
		} catch (Throwable $e) {
			// Correctly thrown exception
			$this->assertInstanceOf(InvalidArgumentException::class, $e);
		}
	}

	/**
	 * Tests StringLengthLessThan validator.
	 */
	public function testStringLengthLessThan(): void
	{
		$good = [
			'žlutý',
			'test',
		];
		$wrong = [
			'test-test-test',
			'ěščřžýáíéíáýž',
		];
		$length = 10;

		$validator = new Validator\StringLengthLessThan($length);
		$this->executeTests($validator, $good, $wrong);
		$this->assertEquals($length, $validator->getMax());

		try {
			$validator->setMax(-10);
			$this->fail(sprintf('Expected exception %s.', InvalidArgumentException::class));
		} catch (AssertionFailedError $e) {
			throw $e;
		} catch (Throwable $e) {
			// Correctly thrown exception
			$this->assertInstanceOf(InvalidArgumentException::class, $e);
		}
	}

	/**
	 * Tests Callback validator.
	 */
	public function testCallback(): void
	{
		$good = [
			1,
			'1',
			'0.1',
		];
		$wrong = [
			new stdClass(),
			false,
			'OLOL',
		];

		$callbacks = [
			static function ($a) {
				return is_numeric($a);
			},
		];

		foreach ($callbacks as $callback) {
			$validator = new Validator\Callback($callback);

			foreach ($good as $value) {
				$this->assertTrue($validator->isValid($value));
			}

			foreach ($wrong as $value) {
				$this->assertFalse($validator->isValid($value));
			}

			$this->assertSame($callback, $validator->getCallback());
			$this->assertSame([], $validator->getAdditionalParams());
		}

		// Test additional parameters
		$good = [3, 9, 33];
		$wrong = [2, 100, true, new stdClass(), 'OHAI'];
		$callback = static function ($value, $divisor) {
			return is_numeric($value) && ($value % $divisor === 0);
		};

		$validator = new Validator\Callback($callback, 3);
		$this->assertSame([3], $validator->getAdditionalParams());

		foreach ($good as $value) {
			$this->assertTrue($validator->isValid($value));
		}

		foreach ($wrong as $value) {
			$this->assertFalse($validator->isValid($value));
		}
	}

	/**
	 * Tests static call usage.
	 */
	public function testCallStatic(): void
	{
		static $value = 42;
		$this->assertTrue(Validator::isInt($value));
		$this->assertTrue(Validator::lessThan($value, $value * 2));
		$this->assertTrue(Validator::callback(
			$value,
			static function ($value, $lowerBound) {
				return is_numeric($value) && $value > $lowerBound;
			},
			41
		));

		// Tests storing in cache - the first on in cached, the second one isn't
		// because it had additional parameters that had been added to the cache ID
		$this->assertNotNull(ObjectCache::get(IsInt::class));
		$this->assertNull(ObjectCache::get(LessThan::class));
	}

	/**
	 * Tests right values.
	 *
	 * @param ValidatorInterface $validator Validator
	 * @param array $good Right values
	 * @param array $wrong Wrong values
	 */
	private function executeTests(ValidatorInterface $validator, array $good, array $wrong): void
	{
		foreach ($good as $value) {
			$this->assertTrue(
				$validator->isValid($value),
				sprintf('Tests of value %s should be true but is false.', print_r($value, true))
			);
		}

		foreach ($wrong as $value) {
			$this->assertFalse(
				$validator->isValid($value),
				sprintf('Tests of value %s should be false but is true.', print_r($value, true))
			);
		}
	}

	/**
	 * Prepares right values for validation - shuffles the array
	 *
	 * @param array $good Values retrieved from the static validator getter
	 * @return array Array ready for validation
	 */
	private function getGoodValues(array $good): array
	{
		$this->assertIsArray($good, 'Variable is not an array.');
		// Adds values again - in reversed order
		$good = array_merge($good, array_reverse($good));
		$count = count($good);
		// Shuffles the array
		$this->assertTrue(shuffle($good), 'Shuffle function failures.');
		// Checks if nothing was lost during shuffle
		$this->assertEquals($count, count($good), 'Some of items lost from array during shuffle function!');

		return $good;
	}

}
