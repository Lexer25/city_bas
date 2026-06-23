<?php
// MODPATH/about/init.php
defined('BAS_VERSION') OR define('BAS_VERSION', '2.0.0');

Route::set('default', '(<controller>(/<action>(/<id>)))')
	->defaults(array(
		'controller' => 'dashboard',
		'action'     => 'index',
	));
	
	
Kohana::$config->load('menu')
    ->set('basip', array(
        'title' => 'basip',
        'url' => '/bas',
        'icon' => 'fa-cog',
        'order' => 200,
		'disabled' => true, 
        'children' => array(
            'tasks' => array(
                'title' => 'Контроль',
                'url' => 'bas'
            ),
            'setting' => array(
                'title' => 'Настройки',
                'url' => 'bas'
            ),
			'config' => array(
                'title' => 'Конфигурация',
                'url' => 'bas/search'
            )
			
        )
    ));