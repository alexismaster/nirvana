<?php
/**
 * Console
 *
 * @category   Nirvana
 * @package    CLI
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\CLI;


class Console
{
    /**
     * @param $text
     */
    public static function println($text = '')
    {
        echo self::_prepareLine($text);
        echo "\r\n";
    }

    /**
     * Цвета
     *
     * @var array
     */
    private static $colors = array(
        'black'     => '0',
        'red'       => '1',
        'green'     => '2',
        'yellow'    => '3',
        'blue'      => '4',
        'purple'    => '5',
        'cyan'      => '6',
        'white'     => '7',
    );

    /**
     * Типы шрифтов
     *
     * @var array
     */
    private static $colorTypes = array('' => 'regular', '_bold' => 'bold', '_under' => 'under', '_bg' => 'bg');

    /**
     * _prepareLine
     *
     * @param $text
     * @return mixed|string
     */
    private static function _prepareLine($text)
    {
        foreach (self::$colors as $name => $value) {
            foreach (self::$colorTypes as $prefix => $type) {
                $text = str_replace('['.$name.$prefix.']', self::_color($value, $type), $text);
            }
        }

        if (PHP_OS === 'Linux') {
            return $text . chr(27) . '[0m'; // Сброс цвета в конце строки
        }
        else {
            return $text;
        }
    }

    /**
     * setColor
     *
     * @param $color
     * @param string $type
     * @return string
     */
    private static function _color($color, $type = 'regular')
    {
        if (PHP_OS !== 'Linux')  return '';

        $out = '0';

        switch ($type) {
            case 'regular'  : $out = '0;3';     break;
            case 'bold'     : $out = '1;3';     break;
            case 'under'    : $out = '4;3';     break;
            case 'bg'       : $out = '4';       break;
        }

        return chr(27) . '[' . $out . $color . 'm';
    }
}