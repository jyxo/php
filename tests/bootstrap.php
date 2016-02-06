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

// Because of SessionTest
session_start();

// Autoload
spl_autoload_register(function($className) {
	if (strpos($className, 'Jyxo') !== 0) {
		return;
	}

	$file = str_replace('\\', '/', $className) . '.php';
	foreach ([realpath(__DIR__ . '/..'), __DIR__] as $dir) {
		$filePath = $dir . '/' . $file;
		if (false !== stream_resolve_include_path($filePath)) {
			require_once $filePath;
		}
	}
});

// File path
define('DIR_FILES', __DIR__ . '/files');
