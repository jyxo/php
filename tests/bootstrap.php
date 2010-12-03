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

// Because of SessionTest
session_start();

// Class search path
set_include_path(
	realpath(__DIR__ . '/..') . PATH_SEPARATOR .   // Libraries
	__DIR__ . PATH_SEPARATOR .   // Library tests
	get_include_path()
);

// Autoload
spl_autoload_register(function($className) {
	$file = str_replace('\\', '/', $className) . '.php';
	$file = str_replace('_', '/', $className) . '.php';
	// Because of non-existent class tests in \Jyxo\Input
	if (false !== stream_resolve_include_path($file)) {
		require_once $file;
	}
});

// UTF-8
mb_internal_encoding('UTF-8');

// File path
define('DIR_FILES', __DIR__ . '/files');
