<?php
/**
 * Nirvana Framework
 *
 * Это что то вроде точки входа. Сюда будут перенаправлены все запросы
 * для которых не существует конкретного файла.
 *
 * Пример настройки nginx-а в файле "nginx.conf".
 *
 * @category   Nirvana
 * @package    ORM
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */


// Смена текушего каталога на родительский. Именно там лежат все исходники.
// Так будет проще подключать их инструкциями require и include
chdir(dirname(__DIR__));


// Настройки автозагрузки
require 'autoloader.php';


// Режим отладдки?
$debugMode = true;


// Запуск приложения
Nirvana\MVC\Application::init(require 'Src/config/dev.config.php')->run($debugMode);
