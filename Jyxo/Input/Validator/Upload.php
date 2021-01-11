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

namespace Jyxo\Input\Validator;

use Jyxo\Input\FilterInterface;
use function _;
use function defined;
use function ini_get;
use function is_uploaded_file;
use function substr;
use const UPLOAD_ERR_EXTENSION;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_NO_TMP_DIR;
use const UPLOAD_ERR_PARTIAL;

/**
 * File upload processing.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class Upload extends AbstractValidator implements FilterInterface, ErrorMessage
{

	/**
	 * Return an error if no file was uploaded at all.
	 *
	 * @var bool
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
	 * @var bool
	 */
	private $failedEmpty = false;

	/**
	 * Sets if a file is required to be uploaded.
	 *
	 * @param bool $flag Does the file have to be uploaded
	 * @return Upload
	 */
	public function requireUpload(bool $flag = true): self
	{
		$this->requireUpload = $flag;

		return $this;
	}

	/**
	 * Checks if the file was successfully uploaded.
	 *
	 * @param \Jyxo\Input\Upload|string $file File index in the $_FILES array
	 * @return bool
	 */
	public function isValid($file): bool
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
			$mul = ($mul === 'M' ? 1048576 : ($mul === 'K' ? 1024 : ($mul === 'G' ? 1073741824 : 1)));
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
	 * Sets that the file fas successfully uploaded.
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
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
	public function getError(): string
	{
		return $this->error;
	}

	/**
	 * Checks if the file was uploaded.
	 *
	 * @param string $file File index in the $_FILES array
	 * @return bool
	 */
	protected function isUploaded(string $file): bool
	{
		// Ugly ugly eeeew yuk hack, that is unfortunately needed sometimes
		return defined('IS_TEST') && IS_TEST ? true : is_uploaded_file($file);
	}

	/**
	 * Sets upload errors.
	 *
	 * @param int $error Error code
	 */
	private function setError(int $error): void
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

}
