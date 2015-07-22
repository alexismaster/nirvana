<?php
/**
 * Настройки маршрутизатора
 */


// Страница регистрации
$router->addRoute('sign-up', new \Nirvana\MVC\Route('/sign-up/',
		array('controller' => 'User', 'action' => 'signUp')));

// Страница успешной регистрации
$router->addRoute('sign-up_success', new \Nirvana\MVC\Route('/sign-up_success/',
		array('controller' => 'User', 'action' => 'signUpSuccess')));

// Страница авторизации
$router->addRoute('login', new \Nirvana\MVC\Route('/login/',
		array('controller' => 'User', 'action' => 'login')));

// Страница выхода с сайта
$router->addRoute('logout', new \Nirvana\MVC\Route('/logout/',
		array('controller' => 'User', 'action' => 'logout')));

// Главная (список постов)
$router->addRoute('index', new \Nirvana\MVC\Route('/',
		array('controller' => 'Post', 'action' => 'index')));

// Создание поста
$router->addRoute('add_post', new \Nirvana\MVC\Route('/post-add/',
		array('controller' => 'Post', 'action' => 'add')));

// Редактирование поста
$router->addRoute('edit_post', new \Nirvana\MVC\Route('/post/:postId/edit',
		array('controller' => 'Post', 'action' => 'edit')));

// Удаление поста
$router->addRoute('delete_post', new \Nirvana\MVC\Route('/post/:postId/delete',
		array('controller' => 'Post', 'action' => 'delete')));

// Просмотр конкретного поста
$router->addRoute('showPost', new \Nirvana\MVC\Route('/post/:id',
		array('controller' => 'Post', 'action' => 'showPost')));

// Создание комментария
$router->addRoute('add-comment', new \Nirvana\MVC\Route('/add-comment/:postId',
		array('controller' => 'Comment', 'action' => 'add')));

// Удаление комментария
$router->addRoute('delete-comment', new \Nirvana\MVC\Route('/comment/:id/delete',
		array('controller' => 'Comment', 'action' => 'delete')));


$router->addRoute('orm-update', new \Nirvana\MVC\Route('/orm-update/',
		array('controller' => 'Default', 'action' => 'orm')));