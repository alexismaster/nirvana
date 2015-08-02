<?php
/**
 * Пример теста
 *
 * https://packagist.org/packages/phpunit/phpunit
 * https://phpunit.de/manual/current/en/textui.html#textui.clioptions
 */

namespace TestNamespace;


class UnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     */
    public function testMethod($data)
    {
        $this->assertTrue($data);
    }

    public function provider()
    {
        return array(
            'my named data' => array(true),
            'my named     ' => array(true),
            'my data'       => array(false)
        );
    }
}