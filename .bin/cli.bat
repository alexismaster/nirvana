::
:: Сценарий запускающий кодогенератор под Windows
::
:: Перед запуском экспортируйте в переменную PATH пусть к PHP, пример:
:: set PATH=%PATH%;C:\Users\user\Desktop\php-5.6
::


echo off
php.exe .\Nirvana\CLI\cli.php %1 %2 %3 %4 %5
