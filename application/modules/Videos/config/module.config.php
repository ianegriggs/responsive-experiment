<?php

return array(
    'router' => array(
		'videos' => array(
			'type'    => '\Core\Router\Regex',
			'options' => array(
				'route'    => 'videos/?',
				'defaults' => array(
					'module' 		=> 'videos',
					'controller'    => 'index',
					'action'        => 'index'
				),
				'reverse' => 'videos/'
			),
		),
		'gifimages' => array(
			'type'    => '\Core\Router\Regex',
			'options' => array(
				'route'    => 'gifs/?',
				'defaults' => array(
					'module' 		=> 'videos',
					'controller'    => 'gifts',
					'action'        => 'index'
				),
				'reverse' => 'gifs/'
			),
		),
    ),

    'view_manager' => array(
        'view_path'             		=> __DIR__ . '/../view/',
    ),
);
