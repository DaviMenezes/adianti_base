<?php
namespace Adianti\Base\Modules\Communication\Model;

use Adianti\Base\Lib\Database\TRecord;

/**
 * SystemDocumentGroup
 *
 * @version    1.0
 * @package    model
 * @subpackage communication
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemDocumentGroup extends TRecord
{
    const TABLENAME = 'system_document_group';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = null, $callObjectLoad = true)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('document_id');
        parent::addAttribute('system_group_id');
    }
}
