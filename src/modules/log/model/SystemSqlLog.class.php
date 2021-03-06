<?php
namespace Adianti\Base\Modules\Log\Model;

use Adianti\Base\Lib\Database\TRecord;
use Adianti\Base\Lib\Log\AdiantiLoggerInterface;
use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Registry\TSession;

/**
 * SystemSqlLog
 *
 * @version    1.0
 * @package    model
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemSqlLog extends TRecord implements AdiantiLoggerInterface
{
    const TABLENAME = 'system_sql_log';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = null, $callObjectLoad = true)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('logdate');
        parent::addAttribute('login');
        parent::addAttribute('database_name');
        parent::addAttribute('sql_command');
        parent::addAttribute('statement_type');
    }
    
    /**
     * Writes an message in the global logger
     * @param  $message Message to be written
     */
    public function write($message)
    {
        $dbname = TTransaction::getDatabase();
        
        // avoid log of log
        if ($dbname !== 'log' and (in_array(substr($message, 0, 6), array('INSERT', 'UPDATE', 'DELETE')))) {
            $time = date("Y-m-d H:i:s");
            
            TTransaction::open('log');
            $object = new self;
            $object->logdate = $time;
            $object->login = TSession::getValue('login');
            $object->database_name = $dbname;
            $object->sql_command = $message;
            $object->statement_type = strtoupper(substr($message, 0, 6));
            $object->store();
            TTransaction::close();
        }
    }
}
