<?php
/**
 * Справка
 */


namespace Nirvana\CLI\Command;

use \Nirvana\CLI as CLI;


class Help extends CLI\Command
{
    /**
     * Точка входа
     */
    public function run()
    {
        if (isset($this->argv[2])) {
            $script = explode('_', $this->argv[2]);
            $script = array_map('ucfirst', $script);
            $script = implode('', $script);
        }

        // Справка по конкретной команде
        if (isset($script) and is_file('Nirvana/CLI/Command/' . $script . '.php')) {
            $class  = 'Nirvana\\CLI\\Command\\' . $script;
            $object = new $class(array());

            CLI\Console::println($object->getDescription());
        }
        else {
            $this->_showIndexPage();
        }

        CLI\Console::println();
        CLI\Console::println();
    }

    /**
     * Возвращает список доступных команд
     *
     * @return array
     */
    private function _getCommands()
    {
        $argv     = array();
        $commands = array();

        foreach (glob('Nirvana/CLI/Command/*.php') as $filename) {
            $class  = 'Nirvana\\CLI\\Command\\' . pathinfo($filename)['filename'];
            $object = new $class($argv);
            $commands[] = $object;
        }

        return $commands;
    }

    /**
     * Главная страница справки
     */
    private function _showIndexPage()
    {
        $commands = $this->_getCommands();

        /*
         * Список команд
         */

        CLI\Console::println('[green_bold] Command List:');
        CLI\Console::println();

        foreach ($commands as $command) {
            CLI\Console::println(' - ' . $command->getSyntax());
        }

        /*
         * Пример использования команд
         */

        CLI\Console::println();
        CLI\Console::println('[green_bold] Sample Usage:');
        CLI\Console::println();

        if (PHP_OS === 'Linux') {
            $cli = '[white] developer@debian:~/www/nirvana_project';
        }
        else {
            $cli = '[white] C:\WebServer\htdocs\nirvana_project>';
        }

        foreach ($commands as $command) {
            CLI\Console::println($cli . ' ' . $command->getExample());
        }
    }


    public function getSyntax()
    {
        return '[green]help [white][CommandName]';
    }

    public function getDescription()
    {
        return '';
    }

    public function getExample()
    {
        return '[cyan]help create_entity';
    }
}



