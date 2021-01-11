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

use function move_uploaded_file;
use function pathinfo;
use const PATHINFO_EXTENSION;

/**
 * Uploaded file.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class Upload
{

	/**
	 * Index in $_FILES superglobal array.
	 *
	 * @var string
	 */
	private $index;

	/**
	 * The file was successfully uploaded.
	 *
	 * @var bool
	 */
	private $success = false;

	/**
	 * Constructor.
	 *
	 * @param string $index File index
	 */
	public function __construct(string $index)
	{
		$this->index = $index;
	}

	/**
	 * Confirms that the file was successfully uploaded.
	 *
	 * @return Upload
	 */
	public function confirmUpload(): self
	{
		// Isset is just a simple check, it is not sufficient!
		$this->success = isset($_FILES[$this->index]);

		return $this;
	}

	/**
	 * Returns file's temporary name.
	 *
	 * @return string
	 */
	public function tmpName(): ?string
	{
		return isset($_FILES[$this->index]) ? $_FILES[$this->index]['tmp_name'] : null;
	}

	/**
	 * Returns upload error type.
	 *
	 * @return int
	 */
	public function error(): ?int
	{
		return isset($_FILES[$this->index]) ? $_FILES[$this->index]['error'] : null;
	}

	/**
	 * Moves the uploaded file.
	 *
	 * @param string $destination File destination
	 * @return bool
	 */
	public function move(string $destination): bool
	{
		$result = false;

		if ($this->success) {
			$result = move_uploaded_file($this->tmpName(), $destination);
		}

		return $result;
	}

	/**
	 * Returns file extension.
	 *
	 * @return string|null
	 */
	public function extension(): ?string
	{
		$ext = null;

		if ($this->success) {
			$ext = pathinfo($_FILES[$this->index]['name'], PATHINFO_EXTENSION);
		}

		return $ext;
	}

	/**
	 * Returns file name.
	 *
	 * @return string|null
	 */
	public function filename(): ?string
	{
		$filename = null;

		if ($this->success) {
			$filename = $_FILES[$this->index]['name'];
		}

		return $filename;
	}

	/**
	 * Conversion to string because of other validators.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->tmpName();
	}

}
