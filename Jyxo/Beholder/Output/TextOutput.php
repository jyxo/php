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

namespace Jyxo\Beholder\Output;

use Jyxo\Beholder\Executor;
use function sprintf;

/**
 * Beholder plain text output class
 *
 * @author Matěj Humpál
 */
class TextOutput extends Output
{

	public function getContentType(): string
	{
		// intentionally left as text/html
		return 'text/html; charset=utf-8';
	}

	private function getPrayer(): string
	{
		$return = '';

		for ($i = 0; $i < 5; $i++) {
			$return .= 'Our Father in heaven,' . "\n";
			$return .= 'hallowed be your name,' . "\n";
			$return .= 'your kingdom come,' . "\n";
			$return .= 'your will be done' . "\n";
			$return .= 'on earth as it is in heaven.' . "\n";
			$return .= 'Give us today our daily bread,' . "\n";
			$return .= 'and forgive us the wrong we have done' . "\n";
			$return .= 'as we forgive those who wrong us.' . "\n";
			$return .= 'Subject us not to the trial' . "\n";
			$return .= 'but deliver us from the evil one.' . "\n";
			$return .= 'And make the ' . $this->result->getProject() . " project work.\n";
			$return .= 'Amen.' . "\n\n";
		}

		return $return;
	}

	public function __toString(): string
	{
		$return = '';

		$return .= '<pre>This is Beholder for project ' . $this->result->getProject() . "\n";
		$return .= 'Tests included: ' . $this->result->getIncludeFilter() . "\n";
		$return .= 'Tests excluded: ' . $this->result->getExcludeFilter() . "\n\n";
		$return .= '<a href="?' . Executor::PARAM_INCLUDE . '=' . $this->result->getIncludeFilter()
			. '&amp;' . Executor::PARAM_EXCLUDE . '=' . $this->result->getExcludeFilter()
			. '&amp;' . Executor::PARAM_OUTPUT . '=' . Executor::OUTPUT_HTML . "\">Html version</a>\n\n";
		$return .= '<a href="?' . Executor::PARAM_INCLUDE . '=' . $this->result->getIncludeFilter()
			. '&amp;' . Executor::PARAM_EXCLUDE . '=' . $this->result->getExcludeFilter()
			. '&amp;' . Executor::PARAM_OUTPUT . '=' . Executor::OUTPUT_JSON . "\">JSON version</a>\n\n";

		$return .= sprintf("%-9s %10s   %-10s %-7s  %-35s    %s\n", 'Run Order', 'Duration', 'Ident', 'Status', 'Test Name', 'Description');

		foreach ($this->testsData as $data) {
			$return .= sprintf(
				"%9d %9.2fs   %-10s %-7s  %-35s    %s\n",
				$data['order'],
				$data['duration'],
				$data['ident'],
				$data['result']->getStatusMessage(),
				$data['test']->getDescription(),
				$data['result']->getDescription()
			);
		}

		if ($this->result->hasAllSucceeded()) {
			$return .= "\nJust a little prayer so we know we are allright.\n\n";
			$return .= $this->getPrayer();
		}

		return $return;
	}

}
