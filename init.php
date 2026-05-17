<?php
// MODPATH/about/init.php
defined('BAS_VERSION') OR define('BAS_VERSION', '2.0.0');

Route::set('default', '(<controller>(/<action>(/<id>)))')
	->defaults(array(
		'controller' => 'dashboard',
		'action'     => 'index',
	));