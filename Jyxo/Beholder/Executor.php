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

namespace Jyxo\Beholder;

use Jyxo\Beholder\Output\HtmlOutput;
use Jyxo\Beholder\Output\JsonOutput;
use Jyxo\Beholder\Output\NoOutput;
use Jyxo\Beholder\Output\Output;
use Jyxo\Beholder\Output\TextOutput;
use Jyxo\Beholder\Result\TestSuiteResult;
use Jyxo\Timer;
use UnexpectedValueException;
use function array_keys;
use function array_multisort;
use function explode;
use function fnmatch;
use function header;
use function shuffle;
use function sprintf;
use const SORT_ASC;

/**
 * Beholder test executor.
 *
 * Includes filtering and HTML output formatting.
 *
 * Tests are performed in random order but results are outputted in alphabetical order with the order they were performed.
 *
 * Example:
 * <code>
 * $beholder = new \Jyxo\Beholder\Executor('Project', $_GET);
 * $beholder->addTest('T1', new \Project\Beholder\Test1('Test 1'));
 * $beholder->addTest('T2', new \Project\Beholder\Test2('Test 2'));
 * $beholder->addTest('T3', new \Project\Beholder\Test3Blah('Test 3 Blah'));
 * $beholder->run();
 * </code>
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Matoušek
 * @author Jaroslav Hanslík
 */
class Executor
{

	/**
	 * Plaintext output.
	 */
	public const OUTPUT_TEXT = 't';

	/**
	 * HTML output.
	 */
	public const OUTPUT_HTML = 'h';

	/**
	 * JSON output.
	 */
	public const OUTPUT_JSON = 'j';

	/**
	 * No output.
	 */
	public const OUTPUT_NOTHING = 'n';

	/**
	 * Output parameter.
	 */
	public const PARAM_OUTPUT = 'o';

	/**
	 * Parameter for including tests.
	 */
	public const PARAM_INCLUDE = 't';

	/**
	 * Parameter for excluding tests.
	 */
	public const PARAM_EXCLUDE = 'nt';

	/**
	 * Project name.
	 *
	 * @var string
	 */
	private $project = '';

	/**
	 * List of tests.
	 *
	 * @var array
	 */
	private $tests = [];

	/**
	 * Filter for including tests.
	 *
	 * @var string
	 */
	private $includeFilter = '*';

	/**
	 * Filter for excluding tests.
	 *
	 * @var string
	 */
	private $excludeFilter = '';

	/**
	 * Output type.
	 *
	 * @var string
	 */
	private $output = self::OUTPUT_HTML;

	/**
	 * Tests data.
	 *
	 * @var array
	 */
	private $testsData = [];

	/**
	 * Constructor.
	 *
	 * @param string $project Project name
	 * @param array $params Parameters; possible parameters are: include, exclude, output
	 */
	public function __construct(string $project, array $params = [])
	{
		// Project name
		$this->project = $project;
		$this->setParams($params);
	}

	public function setParams(array $params): void
	{
		// Filters
		if (!empty($params[self::PARAM_INCLUDE])) {
			$this->includeFilter = (string) $params[self::PARAM_INCLUDE];
		}

		if (!empty($params[self::PARAM_EXCLUDE])) {
			$this->excludeFilter = (string) $params[self::PARAM_EXCLUDE];
		}

		// Output type
		if (empty($params[self::PARAM_OUTPUT])) {
			return;
		}

		switch ($params[self::PARAM_OUTPUT]) {
			// Nothing
			case self::OUTPUT_NOTHING:
				$this->output = self::OUTPUT_NOTHING;

				break;

			// Plaintext

			case self::OUTPUT_TEXT:
				$this->output = self::OUTPUT_TEXT;

				break;

			// JSON

			case self::OUTPUT_JSON:
				$this->output = self::OUTPUT_JSON;

				break;

			// HTML

			case self::OUTPUT_HTML:
			default:
				$this->output = self::OUTPUT_HTML;

				break;
		}
	}

	/**
	 * Performs chosen tests and outputs results according to the selected output type.
	 *
	 * @param bool $print
	 * @return Output
	 */
	public function run(bool $print = true): Output
	{
		// Filters tests
		foreach (array_keys($this->tests) as $ident) {
			if (!$this->includeTest($ident)) {
				unset($this->tests[$ident]);
			}
		}

		// Shuffles them
		$idents = array_keys($this->tests);
		shuffle($idents);

		// Performs tests and gathers results
		$order = 1;
		$allSucceeded = true;

		foreach ($idents as $ident) {
			// Runs a test
			$data = $this->runTest($ident);

			// Saves the overall status
			$allSucceeded = $allSucceeded && $data['result']->isSuccess();

			// Adds the text into the output
			$order++;
			$data['order'] = $order;
			$this->testsData[] = $data;
		}

		// Sorts tests according to their identifiers
		$idents = [];

		foreach ($this->testsData as $key => $data) {
			$idents[$key] = $data['ident'];
		}

		array_multisort($idents, SORT_ASC, $this->testsData);

		// Outputs the header
		if ($allSucceeded) {
			header('HTTP/1.1 200 OK');
		} else {
			header('HTTP/1.1 500 Internal Server Error');
		}

		$result = new TestSuiteResult($this->project, $allSucceeded, $this->testsData, $this->includeFilter, $this->excludeFilter);

		// Outputs the output :)
		switch ($this->output) {
			// No output
			case self::OUTPUT_NOTHING:
				$output = new NoOutput($result);

				break;

			// Plaintext

			case self::OUTPUT_TEXT:
				$output = new TextOutput($result);

				break;

			// JSON

			case self::OUTPUT_JSON:
				$output = new JsonOutput($result);

				break;

			// HTML

			case self::OUTPUT_HTML:
			default:
				$output = new HtmlOutput($result);

				break;
		}

		if ($print) {
			header(sprintf('Content-type: %s', $output->getContentType()));
			echo (string) $output;
		}

		return $output;
	}

	/**
	 * Returns tests data.
	 *
	 * @return array
	 */
	public function getTestsData(): array
	{
		return $this->testsData;
	}

	/**
	 * Adds a test.
	 *
	 * @param string $ident Tests identifier
	 * @param TestCase $test Test instance
	 * @return Executor
	 */
	public function addTest(string $ident, TestCase $test): self
	{
		$this->tests[$ident] = $test;

		return $this;
	}

	/**
	 * Runs a single test.
	 *
	 * @param string $ident Test identifier
	 * @return array
	 */
	private function runTest(string $ident): array
	{
		// Runs the test
		$timer = Timer::start();
		$result = $this->tests[$ident]->run();

		if (!($result instanceof Result)) {
			throw new UnexpectedValueException(
				sprintf('Result %s of the test %s is not a %s instance.', $result, $ident, Result::class)
			);
		}

		// Returns result data
		return [
			'ident' => $ident,
			'test' => $this->tests[$ident],
			'result' => $result,
			'duration' => Timer::stop($timer),
		];
	}

	/**
	 * Checks if the given test will be performed according to the current filter settings.
	 *
	 * @param string $ident Test identifier
	 * @return bool
	 */
	private function includeTest(string $ident): bool
	{
		// If the test is not among the allowed ones, return false
		$include = false;

		foreach (explode(',', $this->includeFilter) as $pattern) {
			if ($this->patternMatch($pattern, $ident)) {
				// We cannot use "return true" because the test might be disabled later
				$include = true;
			}
		}

		if (!$include) {
			return false;
		}

		// If the test is among the excluded ones, return false
		foreach (explode(',', $this->excludeFilter) as $pattern) {
			if ($this->patternMatch($pattern, $ident)) {
				return false;
			}
		}

		// Included otherwise
		return true;
	}

	/**
	 * Checks if the given string matches the given pattern.
	 *
	 * @param string $pattern Pattern
	 * @param string $string String to be matched
	 * @return bool
	 */
	private function patternMatch(string $pattern, string $string): bool
	{
		return fnmatch($pattern, $string);
	}

}
