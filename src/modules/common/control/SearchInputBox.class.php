<?php
namespace Adianti\Base\Modules\Common\Control;

use Adianti\Base\Lib\Control\TPage;
use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Widget\Base\TScript;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Lib\Widget\Form\TEntry;
use Adianti\Base\Lib\Widget\Form\TForm;
use Adianti\Base\Modules\Admin\User\Model\SystemUser;
use Adianti\Base\Widget\Menu\TMenuParser;
use Dvi\Adianti\Helpers\Redirect;
use Dvi\Component\Widget\Util\Action;
use Exception;
use stdClass;

/**
 * SearchBox
 *
 * @version    1.0
 * @package    control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SearchInputBox extends TPage
{
    private $form;
    
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct('search_box');
        $this->form = new TForm('search_box');
        
        $input = new TEntry('input');
        $input->setCompletion(array_values(self::getPrograms()));
        $input->placeholder = 'Search...';
        $input->style = 'height:initial';
        $input->setSize(null);
        $input->setExitAction(new Action(route('/search/input/box/loadprogram')));
        $wa = new TEntry('wa');
        $wa->style='display:none';
        $this->form->add($input);
        $this->form->add($wa);
        $this->form->setFields(array($input));
        parent::add($this->form);
    }
    
    /**
     * Returns an indexed array with all programs
     */
    public static function getPrograms()
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
        $programs = self::getPrograms();
        $program = $param['input'];
        $controller = array_search($program, $programs);
        
        if ($controller) {
            Redirect::ajaxLoadPage($controller);
        }
        
        $data = new stdClass;
        $data->input = '';
        TForm::sendData('search_box', $data, false, false);
        
        TScript::create("$('.search-bar').removeClass('open');");
    }
}
