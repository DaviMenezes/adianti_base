<?php
namespace Adianti\Base\Lib\Core;

use Adianti\Base\Lib\Registry\TSession;

/**
 * Application config
 *
 * @version    5.5
 * @package    core
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class AdiantiApplicationConfig
{
    protected static $config;
    
    /**
     * Load configuration from array
     */
    public static function load($config)
    {
        if (is_array($config)) {
            self::$config = $config;
        }
    }
    
    /**
     * Export configuration
     */
    public static function get()
    {
        $class  = isset($_REQUEST['class']) ? $_REQUEST['class'] : '';
        if ($class == 'BuilderView') {
            self::updateTheme('theme4');
        }
        return self::$config;
    }

    public static function updateTheme(string $theme)
    {
        self::$config['general']['theme'] = $theme;
        TSession::setValue('theme', $theme);
    }

    public static function getTheme()
    {
        return TSession::getValue('theme') ?? self::$config['general']['theme'];
    }

    public static function parseIniFile()
    {
        return self::$config = parse_ini_file('app/config/application.ini', true);
    }
}
