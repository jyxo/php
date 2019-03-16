<?php declare(strict_types=1);

namespace Jyxo\Mail;

trait EmailTestHelpers
{

	/**
	 * Creates a basic email.
	 *
	 * @return \Jyxo\Mail\Email
	 */
	private function getEmail(): \Jyxo\Mail\Email
	{
		$email = new Email();
		$email->setSubject('Novinky září 2009 ... a kreslící soutěž')
			->setFrom(new Email\Address('blog-noreply@blog.cz', 'Blog.cz'))
			->addTo(new Email\Address('test@blog.cz', 'Test Test'))
			->setBody(new Email\Body(\Jyxo\Html::toText($this->content)));

		return $email;
	}

	/**
	 * Compares the actual and expected result.
	 *
	 * @param string $file FileAttachment with the expected result
	 * @param \Jyxo\Mail\Sender\Result $result
	 */
	private function assertResult(string $file, \Jyxo\Mail\Sender\Result $result)
	{
		$expected = file_get_contents($this->filePath . '/' . $file);

		// Replacing some headers that are created dynamically
		$expected = preg_replace('~====b1[a-z0-9]{32}====~', '====b1' . substr($result->messageId, 0, 32) . '====', $expected);
		$expected = preg_replace('~====b2[a-z0-9]{32}====~', '====b2' . substr($result->messageId, 0, 32) . '====', $expected);
		$expected = preg_replace("~Date: [^\n]+~", 'Date: ' . $result->datetime->email, $expected);
		$expected = preg_replace('~Message-ID: <[^>]+>~', 'Message-ID: <' . $result->messageId . '>', $expected);

		$this->assertEquals($expected, $result->source, sprintf('Failed test for file %s.', $file));
	}

}
