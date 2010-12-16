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

namespace Jyxo\Input;

/**
 * Uploaded file.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
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
	 * @var boolean
	 */
	private $success = false;

	/**
	 * Constructor.
	 *
	 * @param string $index File index
	 */
	public function __construct($index)
	{
		$this->index = $index;
	}

	/**
	 * Confirms that the file was successfully uploaded.
	 *
	 * @return \Jyxo\Input\Upload
	 */
	public function confirmUpload()
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
	public function tmpName()
	{
		return isset($_FILES[$this->index]) ? $_FILES[$this->index]['tmp_name'] : null;
	}

	/**
	 * Returns upload error type.
	 *
	 * @return integer
	 */
	public function error()
	{
		return isset($_FILES[$this->index]) ? $_FILES[$this->index]['error'] : null;
	}

	/**
	 * Moves the uploaded file.
	 *
	 * @param string $destination File destination
	 * @return boolean
	 */
	public function move($destination)
	{
		$result = false;
		if ($this->success) {
			$result = move_uploaded_file($this->tmpName(), $destination);
		}
		return $result;
	}

	/**
	 * Conversion to string because of other validators.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->tmpName();
	}

	/**
	 * Returns file extension.
	 *
	 * @return string
	 */
	public function extension()
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
	 * @return string
	 */
	public function filename()
	{
		$filename = null;
		if ($this->success) {
			$filename = $_FILES[$this->index]['name'];
		}
		return $filename;
	}
}
