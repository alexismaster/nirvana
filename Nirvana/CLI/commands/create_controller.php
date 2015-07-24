<?php
/**
 * Скрипт генерации контроллера
 */


$res = array();

$controllers = array_slice($argv, 2);

$controllers = array_map(function ($item) {
  return explode(',', $item);
}, $controllers);


foreach ($controllers as $item) {
  $res = array_merge($res, $item);
}

unset($controllers);




foreach ($res as $name) {
  //var_dump($name);
  $path = "Src/Controller/{$name}Controller.php";

  if (!is_file($path)) {
    file_put_contents($path, $twig->render('controller.twig', array('name' => $name)));
  }
  else {
    echo "Controller \"{$name}Controller\" already exists!\r\n";
  }
}





