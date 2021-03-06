<?php
namespace Adianti\Base\Modules\Admin\User\Model;

use Adianti\Base\Lib\Database\TRecord;

/**
 * SystemUserProgram
 *
 * @version    1.0
 * @package    model
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemUserProgram extends TRecord
{
    const TABLENAME = 'system_user_program';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('system_user_id');
        parent::addAttribute('system_program_id');
    }
}
