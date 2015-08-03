
::
:: Генерация документации (Windows)
::

@echo off

:: Путь к папке с исходниками фреймворка
set SOURCE=Nirvana

:: Папка где будет находиться документация
set TARGET=public\doc

:: Исключаемые файлы
set IGNORE=cli.php,*.twig,*/Twig/*,Twig/*


php.exe phpDocumentor2\bin\phpdoc.php -d %SOURCE% -t %TARGET% -i %IGNORE% --template responsive