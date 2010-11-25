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

// Kvůli SessionTestu
session_start();

// Cesta pro hledání tříd
set_include_path(
	realpath(__DIR__ . '/..') . PATH_SEPARATOR .   // Knihovny
	__DIR__ . PATH_SEPARATOR .   // Testy knihoven
	get_include_path()
);

// Autoload
spl_autoload_register(function($className) {
	$file = str_replace('\\', '/', $className) . '.php';
	$file = str_replace('_', '/', $className) . '.php';
	// Kvůli testům načítání neexistujících tříd v \Jyxo\Input
	if (false !== stream_resolve_include_path($file)) {
		require_once $file;
	}
});

// UTF-8
mb_internal_encoding('UTF-8');

// Cesta k souborům
define('DIR_FILES', __DIR__ . '/files');
