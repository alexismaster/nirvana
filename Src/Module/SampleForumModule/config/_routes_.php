<?php
/**
 * Конфиг модуля SampleForumModule
 */


return array(
	'forum_add_topic' => array('url' => '/forum/topic/add', 'controller' => 'Topic', 'action' => 'add', 'module' => 'SampleForum'),
	'forum_add_forum' => array('url' => '/forum/forum/add', 'controller' => '', 'action' => '', 'module' => 'SampleForum'),
	'forum_add_post'  => array('url' => '/forum/post/add', 'controller' => '', 'action' => '', 'module' => 'SampleForum'),
	//'' => array('url' => '', 'controller' => '', 'action' => ''),
);