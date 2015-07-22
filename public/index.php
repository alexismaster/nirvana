<?php

chdir(dirname(__DIR__));

require 'autoloader.php';


Nirvana\MVC\Application::init(require 'config/dev.config.php')->run('prod');