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

namespace Jyxo\Input\Validator;

/**
 * File upload processing.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class Upload extends \Jyxo\Input\Validator\AbstractValidator implements \Jyxo\Input\FilterInterface, \Jyxo\Input\Validator\ErrorMessage
{
	/**
	 * Return an error if no file was uploaded at all.
	 *
	 * @var boolean
	 */
	private $requireUpload = true;

	/**
	 * File index in the $_FILES array.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Error message in case the validation fails.
	 *
	 * @var string
	 */
	private $error;

	/**
	 * Upload failed, because no file was uploaded; but no file is required.
	 *
	 * @var boolean
	 */
	private $failedEmpty = false;

	/**
	 * Sets if a file is required to be uploaded.
	 *
	 * @param boolean $flag Does the file have to be uploaded
	 * @return \Jyxo\Input\Validator\Upload
	 */
	public function requireUpload($flag = true)
	{
		$this->requireUpload = $flag;
		return $this;
	}

	/**
	 * Checks if the file was successfully uploaded.
	 *
	 * @param \Jyxo\Input\Upload|string $file File index in the $_FILES array
	 * @return boolean
	 */
	public function isValid($file)
	{
		$valid = false;
		if (!$file instanceof \Jyxo\Input\Upload) {
			$file = new \Jyxo\Input\Upload($file);
		}
		if ($file->tmpName() && $this->isUploaded($file->tmpName())) {
			$valid = true;
		} else {
			$postMaxSize = ini_get('post_max_size');
			$mul = substr($postMaxSize, -1);
			$mul = ($mul == 'M' ? 1048576 : ($mul == 'K' ? 1024 : ($mul == 'G' ? 1073741824 : 1)));
			if (isset($_SERVER['CONTENT_LENGTH'])) {
				if ($_SERVER['CONTENT_LENGTH'] > $mul * (int) $postMaxSize && $postMaxSize) {
					$this->error = _('The file you are trying to upload is too big.');
				} else {
					$this->setError($file->error());
				}
			} elseif ($this->requireUpload) {
				$this->error = _('No file was uploaded.');
			} else {
				$this->failedEmpty = true;
			}
		}

		return $valid;
	}

	/**
	 * Sets upload errors.
	 *
	 * @param integer $error Error code
	 */
	private function setError($error)
	{
		switch ($error) {
			case UPLOAD_ERR_PARTIAL:
				$this->error = _('The uploaded file was only partially uploaded.');
				break;
			case UPLOAD_ERR_NO_FILE:
				if ($this->requireUpload) {
					$this->error = _('No file was uploaded.');
				} else {
					$this->failedEmpty = true;
				}
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$this->error = _('Missing a temporary folder.');
				break;
			case UPLOAD_ERR_EXTENSION:
				$this->error = _('File upload stopped by extension.');
				break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$this->error = _('The file you are trying to upload is too big.');
				break;
			default:
				$this->error = _('Unknown upload error.');
				break;
		}
	}

	/**
	 * Checks if the file was uploaded.
	 *
	 * @param string $file File index in the $_FILES array
	 * @return boolean
	 */
	protected function isUploaded($file)
	{
		// Ugly ugly eeeew yuk hack, that is unfortunately needed sometimes
		if (defined('IS_TEST') && IS_TEST) {
			return true;
		} else {
			return is_uploaded_file($file);
		}
	}

	/**
	 * Sets that the file fas successfully uploaded.
	 *
	 * @param \Jyxo\Input\Upload $in Uploaded file
	 * @return \Jyxo\Input\Upload
	 */
	public function filter($in)
	{
		if (!$this->error && !$this->failedEmpty) {
			$in->confirmUpload();
		}
		return $in;
	}

	/**
	 * Returns error message in case of upload error.
	 *
	 * @return string
	 */
	public function getError()
	{
		return $this->error;
	}
}
