<?php
/**
 * Настройки маршрутизатора
 */


return array(
	// Пользователи
	'sign-up'           => array('url' => '/sign-up/',              'controller' => 'User',     'action' => 'signUp'),
	'sign-up_success'   => array('url' => '/sign-up_success/',      'controller' => 'User',     'action' => 'signUpSuccess'),
	'login'             => array('url' => '/login/',                'controller' => 'User',     'action' => 'login'),
	'logout'            => array('url' => '/logout/',               'controller' => 'User',     'action' => 'logout'),
	'index'             => array('url' => '/',                      'controller' => 'Post',     'action' => 'index'),
	// Посты
	'add_post'          => array('url' => '/post-add/',             'controller' => 'Post',     'action' => 'add'),
	'edit_post'         => array('url' => '/post/:postId/edit',     'controller' => 'Post',     'action' => 'edit'),
	'delete_post'       => array('url' => '/post/:postId/delete',   'controller' => 'Post',     'action' => 'delete'),
	// Комментарии
	'showPost'          => array('url' => '/post/:id',              'controller' => 'Post',     'action' => 'showPost'),
	'add-comment'       => array('url' => '/add-comment/:postId',   'controller' => 'Comment',  'action' => 'add'),
	'delete-comment'    => array('url' => '/comment/:id/delete',    'controller' => 'Comment',  'action' => 'delete'),
	//'orm-update'        => array('url' => '/orm-update/',           'controller' => 'Default',  'action' => 'orm'),
);