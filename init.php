<?php
// MODPATH/about/init.php
defined('BAS_VERSION') OR define('BAS_VERSION', '2.0.3');

	
	
Kohana::$config->load('menu')
    ->set('basip', array(
        'title' => 'basip',
        'url' => 'bas',
        'icon' => 'fa-cog',
        'order' => 200,
		'disabled' => false, 
		 'children' => array(
            'tasks' => array(
                'title' => 'Контроль',
                'url' => 'bas'
            ),
            'setting' => array(
                'title' => 'Отладка',
                'url' => 'bastest'
            ),
			
			
        )

    ));