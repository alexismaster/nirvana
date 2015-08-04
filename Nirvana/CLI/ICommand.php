<?php
/**
 * ICommand
 *
 * @category   Nirvana
 * @package    CLI.Command
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\CLI;


interface ICommand
{
    /**
     * Точка входа
     *
     * Через данный метод начинается выполнение команды
     */
    public function run();

    /**
     * Синтаксис команды
     *
     * @return string
     */
    public function getSyntax();

    /**
     * Описание команды
     *
     * @return string
     */
    public function getDescription();

    /**
     * Пример использования
     *
     * @return string
     */
    public function getExample();
}