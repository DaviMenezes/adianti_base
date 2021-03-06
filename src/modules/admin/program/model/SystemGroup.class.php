<?php
namespace Adianti\Base\Modules\Admin\Program\Model;

use Adianti\Base\Lib\Database\TCriteria;
use Adianti\Base\Lib\Database\TFilter;
use Adianti\Base\Lib\Database\TRecord;
use Adianti\Base\Lib\Database\TRepository;
use Adianti\Base\Modules\Admin\Model\Collection;
use Adianti\Base\Modules\Admin\Model\Instance;
use Adianti\Base\Modules\Admin\Program\Model\SystemGroupProgram;
use Adianti\Base\Modules\Admin\Program\Model\SystemProgram;
use Adianti\Base\Modules\Admin\User\Model\SystemUserGroup;

/**
 * SystemGroup
 *
 * @version    1.0
 * @package    model
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemGroup extends TRecord
{
    const TABLENAME  = 'system_group';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('name');
    }

    /**
     * Add a SystemProgram to the SystemGroup
     * @param $object Instance of SystemProgram
     */
    public function addSystemProgram(SystemProgram $systemprogram)
    {
        $object = new SystemGroupProgram;
        $object->system_program_id = $systemprogram->id;
        $object->system_group_id = $this->id;
        $object->store();
    }
    
    /**
     * Return the SystemProgram's
     * @return Collection of SystemProgram
     */
    public function getSystemPrograms()
    {
        $system_programs = array();
        
        // load the related System_program objects
        $repository = new TRepository(SystemGroupProgram::class);
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_group_id', '=', $this->id));
        $system_group_system_programs = $repository->load($criteria);
        if ($system_group_system_programs)
        {
            foreach ($system_group_system_programs as $system_group_system_program)
            {
                $system_programs[] = new SystemProgram( $system_group_system_program->system_program_id );
            }
        }
        
        return $system_programs;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        // delete the related objects
        SystemGroupProgram::where('system_group_id', '=', $this->id)->delete();
    }
    
    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_groupSystem_program objects
        $id = isset($id) ? $id : $this->id;
        
        SystemGroupProgram::where('system_group_id', '=', $id)->delete();
        \Adianti\Base\Modules\Admin\User\Model\SystemUserGroup::where('system_group_id', '=', $id)->delete();
        
        // delete the object itself
        parent::delete($id);
    }
}
