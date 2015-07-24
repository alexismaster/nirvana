<?php


if (!isset($argv[2])) {
	exit("Not name module\r\n\r\n");
}


if (is_dir("Src/Module/{$argv[2]}Module")) {
	exit("Module already exists!\r\n\r\n");
}

$name = $argv[2];


// Структура каталогов
mkdir("Src/Module/{$name}Module", 0777);
mkdir("Src/Module/{$name}Module/config", 0777);
mkdir("Src/Module/{$name}Module/Controller", 0777);
mkdir("Src/Module/{$name}Module/Entity", 0777);
mkdir("Src/Module/{$name}Module/views", 0777);



// _routes_.php
file_put_contents("Src/Module/{$name}Module/config/_routes_.php", $twig->render('routes.twig', array('name' => $name)));