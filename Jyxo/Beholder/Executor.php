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

namespace Jyxo\Beholder;

/**
 * Beholder test executor.
 *
 * Includes filtering and HTML output formatting.
 *
 * Tests are performed in random order but results are outputted in alphabetical order with the order they were performed.
 *
 * Usage example:
 * <code>
 *   $beholder = new \Jyxo\Beholder\Executor('Project', $_GET);
 *   $beholder->addTest('T1', new \Project\Beholder\Test1('Test 1'));
 *   $beholder->addTest('T2', new \Project\Beholder\Test2('Test 2'));
 *   $beholder->addTest('T3', new \Project\Beholder\Test3Blah('Test 3 Blah'));
 *   $beholder->run();
 * </code>
 *
 * @category Jyxo
 * @package Jyxo\Beholder
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Matoušek
 * @author Jaroslav Hanslík
 */
class Executor
{
	/**
	 * Plaintext output.
	 *
	 * @var string
	 */
	const OUTPUT_TEXT = 't';

	/**
	 * HTML output.
	 *
	 * @var string
	 */
	const OUTPUT_HTML = 'h';

	/**
	 * Output parameter.
	 *
	 * @var string
	 */
	const PARAM_OUTPUT = 'o';

	/**
	 * Parameter for including tests.
	 *
	 * @var string
	 */
	const PARAM_INCLUDE = 't';

	/**
	 * Parameter for excluding tests.
	 *
	 * @var string
	 */
	const PARAM_EXCLUDE = 'nt';

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
	private $tests = array();

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
	 * Constructor.
	 *
	 * @param string $project Project name
	 * @param array $params Parameters; possible parameters are: include, exclude, output
	 */
	public function __construct($project, array $params = array())
	{
		// Project name
		$this->project = (string) $project;

		// Filters
		if (!empty($params[self::PARAM_INCLUDE])) {
			$this->includeFilter = (string) $params[self::PARAM_INCLUDE];
		}
		if (!empty($params[self::PARAM_EXCLUDE])) {
			$this->excludeFilter = (string) $params[self::PARAM_EXCLUDE];
		}

		// Output type
		if (!empty($params[self::PARAM_OUTPUT])) {
			switch ($params[self::PARAM_OUTPUT]) {
				// Plaintext
				case self::OUTPUT_TEXT:
					$this->output = self::OUTPUT_TEXT;
					break;
				// HTML
				case self::OUTPUT_HTML:
				default:
					$this->output = self::OUTPUT_HTML;
					break;
			}
		}
	}

	/**
	 * Performs chosen tests and outputs results according to the selected output type.
	 *
	 * @return boolean Returns if all tests were successful
	 */
	public function run()
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
		$outputData = array();
		$order = 1;
		$allSucceeded = true;
		foreach ($idents as $ident) {
			// Runs a test
			$data = $this->runTest($ident);

			// Saves the overall status
			$allSucceeded = $allSucceeded && $data['result']->isSuccess();

			// Adds the text into the output
			$data['order'] = $order++;
			$outputData[] = $data;
		}

		// Sorts tests according to their identifiers
		$idents = array();
		foreach ($outputData as $key => $data) {
			$idents[$key] = $data['ident'];
		}
		array_multisort($idents, SORT_ASC, $outputData);

		// Outputs the header
		if ($allSucceeded) {
			header('HTTP/1.1 200 OK');
		} else {
			header('HTTP/1.1 500 Internal Server Error');
		}

		// Outputs the output :)
		switch ($this->output) {
			// Plaintext
			case self::OUTPUT_TEXT:
				$this->writeText($allSucceeded, $outputData);
				break;
			// HTML
			case self::OUTPUT_HTML:
			default:
				$this->writeHtml($allSucceeded, $outputData);
				break;
		}

		return $allSucceeded;
	}

	/**
	 * Adds a test.
	 *
	 * @param string $ident Tests identifier
	 * @param \Jyxo\Beholder\TestCase $test Test instance
	 * @return \Jyxo\Beholder\Executor
	 */
	public function addTest($ident, \Jyxo\Beholder\TestCase $test)
	{
		$this->tests[(string) $ident] = $test;

		return $this;
	}

	/**
	 * Runs a single test.
	 *
	 * @param string $ident Test identifier
	 * @return array
	 * @throws \UnexpectedValueException If the test returned an unknown result value
	 */
	private function runTest($ident)
	{
		// Runs the test
		$timer = \Jyxo\Timer::start();
		$result = $this->tests[$ident]->run();
		if (!($result instanceof \Jyxo\Beholder\Result)) {
			throw new \UnexpectedValueException(sprintf('Result %s of the test %s is not a \Jyxo\Beholder\Result instance.', $result, $ident));
		}

		// Returns result data
		return array(
			'ident' => $ident,
			'test' => $this->tests[$ident],
			'result' => $result,
			'duration' => \Jyxo\Timer::stop($timer)
		);
	}

	/**
	 * Checks if the given test will be performed according to the current filter settings.
	 *
	 * @param string $ident Test identifier
	 * @return boolean
	 */
	private function includeTest($ident)
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
	 * @return boolean
	 */
	private function patternMatch($pattern, $string)
	{
		return fnmatch($pattern, $string);
	}

	/**
	 * Outputs results in HTML form.
	 *
	 * @param boolean $allSucceeded Have all tests been successful
	 * @param array $outputData Test results
	 */
	private function writeHtml($allSucceeded, array $outputData)
	{
		header('Content-Type: text/html; charset=utf-8');
		echo '<head>' . "\n";
		echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />' . "\n";
		echo '<title>Beholder for project ' . $this->project . '</title>' . "\n";
		echo '<style>' . "\n";
		echo '	body {font: 12px Verdana, Geneva, Arial, Helvetica, sans-serif;}' . "\n";
		echo '	table {font-size: small; border-collapse: collapse;}' . "\n";
		echo '	table th {border: 1px solid #000; background: #000; color: #fff;}' . "\n";
		echo '	table td {border: 1px solid #000; padding: .25em .5em;}' . "\n";
		echo '</style>' . "\n";
		echo '</head>' . "\n";
		echo '<body style="background-color: ' . ($allSucceeded ? '#ccffcc' : '#ffcccc') . '; width: 90%; height: 100%; padding: 1em; margin: 0;">' . "\n";
		echo '<h1>Beholder for project ' . $this->project . "</h1>\n";
		echo '<p>Tests included: ' . $this->includeFilter . "\n";
		echo '<br>Tests excluded: ' . $this->excludeFilter . "\n";
		echo '</p>' . "\n";
		echo '<table><tr><th>Run order</th><th>Duration</th><th>Ident</th><th>Status</th><th>Test name</th><th>Comment</th></tr>' . "\n";
		foreach ($outputData as $data) {
			echo sprintf('
				<tr>
					<td>%d</td>
					<td>%.2fs</td>
					<td>%s</td>
					<td style="color: %s;">%s</td>
					<td><b>%s</b></td>
					<td><i>%s</i></td>
				</tr>' . "\n",
				$data['order'],
				$data['duration'],
				$data['ident'],
				$data['result']->isSuccess() ? 'green' : 'red; font-weight: bold;', $data['result']->getStatusMessage(),
				$data['test']->getDescription(),
				$data['result']->getDescription()
			);
		}
		echo '</table>
			<h2>Parameters</h2>
				<dl>
				<dt>' . self::PARAM_INCLUDE . '</dt>
				<dd>Tests to include, list of shell patterns separated by comma, default *</dd>
				<dt>' . self::PARAM_EXCLUDE . '</dt>
				<dd>Tests to exclude, empty by default</dd>
				<dt>' . self::PARAM_OUTPUT . '</dt>
				<dd>' . self::OUTPUT_HTML . ' = HTML output, ' . self::OUTPUT_TEXT . ' = text output</dd>
				</dl>
			<p>Tests are included, then excluded.</p>
			<p><a href="?' . self::PARAM_INCLUDE . '=' . $this->includeFilter
				. '&amp;' . self::PARAM_EXCLUDE . '=' . $this->excludeFilter
				. '&amp;' . self::PARAM_OUTPUT . '=' . self::OUTPUT_TEXT . '">Text version</a></p>
			</body>' . "\n";
	}

	/**
	 * Outputs results in plaintext.
	 *
	 * @param boolean $allSucceeded Have all tests been successful
	 * @param array $outputData Test results
	 */
	private function writeText($allSucceeded, array $outputData)
	{
		// HTML is sent on purpose
		header('Content-Type: text/html; charset=utf-8');
		echo '<pre>This is Beholder for project ' . $this->project . "\n";
		echo 'Tests included: ' . $this->includeFilter . "\n";
		echo 'Tests excluded: ' . $this->excludeFilter . "\n\n";
		echo '<a href="?' . self::PARAM_INCLUDE . '=' . $this->includeFilter
			. '&amp;' . self::PARAM_EXCLUDE . '=' . $this->excludeFilter
			. '&amp;' . self::PARAM_OUTPUT . '=' . self::OUTPUT_HTML . "\">Html version</a>\n\n";

		echo sprintf("%-9s %10s   %-10s %-7s  %-35s    %s\n",
			'Run Order', 'Duration', 'Ident', 'Status', 'Test Name', 'Description');
		foreach ($outputData as $data) {
			echo sprintf("%9d %9.2fs   %-10s %-7s  %-35s    %s\n",
				$data['order'],
				$data['duration'],
				$data['ident'],
				$data['result']->getStatusMessage(),
				$data['test']->getDescription(),
				$data['result']->getDescription());
		}

		if ($allSucceeded) {
			echo "\nJust a little prayer so we know we are allright.\n\n";

			for ($i = 0; $i < 5; $i++) {
				echo 'Our Father in heaven,' . "\n";
				echo 'hallowed be your name,' . "\n";
				echo 'your kingdom come,' . "\n";
				echo 'your will be done' . "\n";
				echo 'on earth as it is in heaven.' . "\n";
				echo 'Give us today our daily bread,' . "\n";
				echo 'and forgive us the wrong we have done' . "\n";
				echo 'as we forgive those who wrong us.' . "\n";
				echo 'Subject us not to the trial' . "\n";
				echo 'but deliver us from the evil one.' . "\n";
				echo 'And make the ' . $this->project . " project work.\n";
				echo 'Amen.' . "\n\n";
			}
		}
	}
}
