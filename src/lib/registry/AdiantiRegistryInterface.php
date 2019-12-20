<?php
namespace Adianti\Base\Lib\Registry;

/**
 * Registry interface
 *
 * @version    5.5
 * @package    registry
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
interface AdiantiRegistryInterface
{
    public static function enabled();
    public static function setValue($key, $value);
    public static function getValue($key);
    public static function delValue($key);
    public static function clear();
}
