<?php
namespace Adianti\Base\Lib\Log;

/**
 * Log Interface
 *
 * @version    5.0
 * @package    log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
interface AdiantiLoggerInterface
{
    public function write($message);
}
