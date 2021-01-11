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
 * Beholder HTML output class
 *
 * @author Matěj Humpál
 */
class HtmlOutput extends Output
{

	public function getContentType(): string
	{
		return 'text/html; charset=utf-8';
	}

	public function __toString(): string
	{
		$return = '';

		$return .= '<head>' . "\n";
		$return .= '<meta http-equiv="content-type" content="text/html; charset=utf-8" />' . "\n";
		$return .= '<title>Beholder for project ' . $this->result->getProject() . '</title>' . "\n";
		$return .= '<style>' . "\n";
		$return .= '	body {font: 12px Verdana, Geneva, Arial, Helvetica, sans-serif;}' . "\n";
		$return .= '	table {font-size: small; border-collapse: collapse;}' . "\n";
		$return .= '	table th {border: 1px solid #000; background: #000; color: #fff;}' . "\n";
		$return .= '	table td {border: 1px solid #000; padding: .25em .5em;}' . "\n";
		$return .= '</style>' . "\n";
		$return .= '</head>' . "\n";
		$return .= '<body style="background-color: ' . ($this->result->hasAllSucceeded() ? '#ccffcc' : '#ffcccc') . '; width: 90%; height: 100%; padding: 1em; margin: 0;">' . "\n";
		$return .= '<h1>Beholder for project ' . $this->result->getProject() . "</h1>\n";
		$return .= '<p>Tests included: ' . $this->result->getIncludeFilter() . "\n";
		$return .= '<br>Tests excluded: ' . $this->result->getExcludeFilter() . "\n";
		$return .= '</p>' . "\n";
		$return .= '<table><tr><th>Run order</th><th>Duration</th><th>Ident</th><th>Status</th><th>Test name</th><th>Comment</th></tr>' . "\n";

		foreach ($this->result->getTestsData() as $data) {
			$return .= sprintf(
				'
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
				$data['result']->isSuccess() ? 'green' : 'red; font-weight: bold;',
				$data['result']->getStatusMessage(),
				$data['test']->getDescription(),
				$data['result']->getDescription()
			);
		}

		$return .= '</table>
			<h2>Parameters</h2>
				<dl>
				<dt>' . Executor::PARAM_INCLUDE . '</dt>
				<dd>Tests to include, list of shell patterns separated by comma, default *</dd>
				<dt>' . Executor::PARAM_EXCLUDE . '</dt>
				<dd>Tests to exclude, empty by default</dd>
				<dt>' . Executor::PARAM_OUTPUT . '</dt>
				<dd>' . Executor::OUTPUT_HTML . ' = HTML output, ' . Executor::OUTPUT_TEXT . ' = text output, ' . Executor::OUTPUT_JSON . ' = JSON output</dd>
				</dl>
			<p>Tests are included, then excluded.</p>
			<p><a href="?' . Executor::PARAM_INCLUDE . '=' . $this->result->getIncludeFilter()
			. '&amp;' . Executor::PARAM_EXCLUDE . '=' . $this->result->getExcludeFilter()
			. '&amp;' . Executor::PARAM_OUTPUT . '=' . Executor::OUTPUT_TEXT . '">Text version</a></p>
			<p><a href="?' . Executor::PARAM_INCLUDE . '=' . $this->result->getIncludeFilter()
			. '&amp;' . Executor::PARAM_EXCLUDE . '=' . $this->result->getExcludeFilter()
			. '&amp;' . Executor::PARAM_OUTPUT . '=' . Executor::OUTPUT_JSON . '">JSON version</a></p>
			</body>' . "\n";

		return $return;
	}

}
