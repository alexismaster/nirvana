<?php
/**
 * Команда создания сущности
 */

namespace Nirvana\CLI\Command;

use \Nirvana\CLI as CLI;


class CreateEntity extends CLI\Command
{
    /**
     * Точка входа
     */
    public function run()
    {
        $res = $this->getNames();

        if (!count($res)) {
            exit("Undefined entity name.\r\n");
        }

        foreach ($res as $name) {

            if (!preg_match('/[A-z]{3}/', $name)) {
                echo "Wrong entity name \"{$name}\".\r\n";
                continue;
            }

            // Сущность модуля
            if ($this->isModule) {

                if (!is_dir("Src/Module/{$this->moduleName}Module")) {
                    exit("Module {$this->moduleName} not found.");
                }

                $path = "Src/Module/{$this->moduleName}Module/Entity/{$name}.php";

                if (is_file($path)) {
                    echo "Entity \"{$name}\" already exists!\r\n";
                    continue;
                }

                $this->createFile($path, 'module_entity.twig', array('name' => $name, 'module' => $this->moduleName));
            } // Обычная сущность
            else {
                $path = "Src/Entity/{$name}.php";

                if (is_file($path)) {
                    echo "Entity \"{$name}\" already exists!\r\n";
                    continue;
                }

                $this->createFile($path, 'entity.twig', array('name' => $name));
            }
        }
    }

    public function getSyntax()
    {
        return '[green]create_entity [cyan]Name1,Name2,NameN [white][--module NameM]';
    }

    public function getDescription()
    {
        return '';
    }

    public function getExample()
    {
        return '[cyan]create_entity Product,Category --module Catalog';
    }
}