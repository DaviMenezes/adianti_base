<?php
namespace Adianti\Base\Modules\Common\Control;

use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Control\TPage;
use Adianti\Base\Lib\Core\TApplication;
use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Lib\Widget\Form\TForm;
use Adianti\Base\Lib\Widget\Form\TMultiSearch;
use Adianti\Base\Lib\Widget\Menu\TMenuParser;
use Adianti\Base\Modules\Admin\Model\SystemUser;
use Exception;

/**
 * SearchBox
 *
 * @version    1.0
 * @package    control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SearchBox extends TPage
{
    private $form;
    
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct('search_box');
        $this->form = new TForm('search_box');
        
        $input = new TMultiSearch('input');
        $input->setSize(240, 28);
        $input->addItems($this->getPrograms());
        $input->setMinLength(1);
        $input->setMaxSize(1);
        $input->setChangeAction(new TAction(array('SearchBox', 'loadProgram')));
        
        $this->form->add($input);
        $this->form->setFields(array($input));
        parent::add($this->form);
    }

    /**
     * Returns an indexed array with all programs
     * @throws \Exception
     */
    public function getPrograms()
    {
        try {
            TTransaction::open('permission');
            $user = SystemUser::newFromLogin(TSession::getValue('login'));
            $programs = $user->getProgramsList();
            
            $menu = new TMenuParser('menu.xml');
            $menu_programs = $menu->getIndexedPrograms();
            
            foreach ($programs as $program => $label) {
                if (!isset($menu_programs[$program])) {
                    unset($programs[$program]);
                }
            }
            
            TTransaction::close();
            return $programs;
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Load an specific program
     */
    public static function loadProgram($param)
    {
        if (isset($param['input'])) {
            $program = $param['input'][0];
            if ($program) {
                TApplication::loadPage($program);
            }
        }
    }
}
