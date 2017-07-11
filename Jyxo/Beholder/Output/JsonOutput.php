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

/**
 * Beholder Json output class
 *
 * @category Jyxo
 * @package Jyxo\Beholder
 * @author Matěj Humpál
 */
class JsonOutput extends \Jyxo\Beholder\Output\Output
{

	public function getContentType(): string
	{
		return 'application/json; charset=utf-8';
	}

	public function __toString(): string
	{
		$tests = [];

		foreach ($this->result->getTestsData() as $data) {
			$tests[] = [
				'order' => $data['order'],
				'duration' => sprintf("%.6f s", $data['duration']),
				'ident' => $data['ident'],
				'result' => $data['result']->getStatusMessage(),
				'test_description' => $data['test']->getDescription(),
				'result_description' => $data['result']->getDescription(),
			];
		}

		$data = [
			'included' => $this->result->getIncludeFilter(),
			'excluded' => $this->result->getExcludeFilter(),
			'tests' => $tests,
			'urls' => [
				'text' => '?' . Executor::PARAM_INCLUDE . '=' . $this->result->getIncludeFilter()
					. '&amp;' . Executor::PARAM_EXCLUDE . '=' . $this->result->getExcludeFilter()
					. '&amp;' . Executor::PARAM_OUTPUT . '=' . Executor::OUTPUT_TEXT,
				'html' => '?' . Executor::PARAM_INCLUDE . '=' . $this->result->getIncludeFilter()
					. '&amp;' . Executor::PARAM_EXCLUDE . '=' . $this->result->getExcludeFilter()
					. '&amp;' . Executor::PARAM_OUTPUT . '=' . Executor::OUTPUT_HTML,
			]
		];

		return json_encode($data);
	}
}
