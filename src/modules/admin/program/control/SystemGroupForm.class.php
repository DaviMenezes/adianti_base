<?php
namespace Adianti\Base\Modules\Admin\Program\Control;

use Adianti\Base\Lib\Base\TStandardSeek;
use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Control\TPage;
use Adianti\Base\Lib\Control\TWindow;
use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Validator\TRequiredValidator;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Base\TScript;
use Adianti\Base\Lib\Widget\Container\THBox;
use Adianti\Base\Lib\Widget\Container\TPanelGroup;
use Adianti\Base\Lib\Widget\Container\TTableRow;
use Adianti\Base\Lib\Widget\Container\TVBox;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Lib\Widget\Form\TButton;
use Adianti\Base\Lib\Widget\Form\TEntry;
use Adianti\Base\Lib\Widget\Form\TForm;
use Adianti\Base\Lib\Widget\Form\TFormSeparator;
use Adianti\Base\Lib\Widget\Form\TLabel;
use Adianti\Base\Lib\Widget\Util\TXMLBreadCrumb;
use Adianti\Base\Lib\Widget\Wrapper\TDBSeekButton;
use Adianti\Base\Lib\Widget\Wrapper\TQuickGrid;
use Adianti\Base\Lib\Wrapper\BootstrapFormBuilder;
use Adianti\Base\Modules\Admin\Program\Model\SystemGroup;
use Adianti\Base\Modules\Admin\Program\Model\SystemProgram;
use App\Http\Request;
use Dvi\Adianti\Helpers\Redirect;
use Dvi\Adianti\Helpers\Reflection;
use Dvi\Adianti\Widget\Form\Button;
use Dvi\Adianti\Widget\Util\Action;
use Dvi\Adianti\Widget\Wrapper\DBSeekButton;
use Exception;
use stdClass;

/**
 * SystemGroupForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemGroupForm extends TPage
{
    protected $form; // form
    protected $program_list;
    protected  static $window;

    /**
     * Class constructor
     * Creates the page and the registration form
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        // creates the form
        $this->form = new BootstrapFormBuilder('form_System_group');
        $this->form->setFormTitle(_t('Group'));

        // create the form fields
        $id   = new TEntry('id');
        $name = new TEntry('name');
        $route = urlRoute('/admin/system/group/form/open/window/program');
        $program_id = new DBSeekButton($route, 'program_id', 'permission', 'form_System_group', SystemProgram::class, 'name');
        $program_id->setReceiveKey('program_id');
        $program_id->setReceiveDisplayField('program_name');
        $program_id->createAction();
        $program_id->getAction()->setParameter('static', 1);

        $program_name = new TEntry('program_name');
        $program_id->setSize('50');
        $program_name->setSize('calc(100% - 200px)');
        $program_name->setEditable(false);
        
        // define the sizes
        $id->setSize('30%');
        $name->setSize('70%');

        // validations
        $name->addValidation('name', new TRequiredValidator);
        
        // outras propriedades
        $id->setEditable(false);
        
        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel(_t('Name'))], [$name]);
        
        $this->program_list = new TQuickGrid();
        $this->program_list->setHeight(200);
        $this->program_list->makeScrollable();
        $this->program_list->style='width: 100%';
        $this->program_list->id = 'program_list';
        $this->program_list->disableDefaultClick();
        $this->program_list->addQuickColumn('', 'delete', 'center', '5%');
        $this->program_list->addQuickColumn('Id', 'id', 'left', '10%');
        $this->program_list->addQuickColumn(_t('Program'), 'name', 'left', '85%');
        $this->program_list->createModel();
        
        $add_button  = new Button('add');
        $add_button->setAction(new Action(route('/admin/system/group/form/program/add'), 'POST'));
        $add_button->icon('fa:plus green');
        $add_button->setLabel('Adicionar');
        
        $hbox = new THBox;
        $hbox->add($program_id);
        $hbox->add($program_name, 'display:initial');
        $hbox->add($add_button);
        $hbox->style = 'margin: 4px';
        
        $vbox = new TVBox;
        $vbox->style='width:100%';
        $vbox->add($hbox);
        $vbox->add(TPanelGroup::pack('', $this->program_list));
        
        $this->form->addFields([new TFormSeparator(_t('Programs'))]);
        $this->form->addFields([$vbox]);
        
        $btn = $this->form->addAction(_t('Save'), new Action(route('/admin/system/group/save'), 'POST'), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addAction(_t('Clear'), new Action(route('/admin/system/group/clear'), 'POST'), 'fa:eraser red');
        $this->form->addActionLink(_t('Back'), new Action(urlRoute('/admin/system/group')), 'fa:arrow-circle-o-left blue');
        
        $this->form->addField($program_id);
        $this->form->addField($program_name);
        $this->form->addField($add_button);
        
        $container = new TVBox;
        $container->style = 'width:90%';
        $container->add(new TXMLBreadCrumb('menu.xml', route('/admin/system/group')));
        $container->add($this->form);
        
        // add the form to the page
        parent::add($container);
    }

    public static function openWindowProgram($param)
    {
        $window = new TStandardSeek();
        $pagination_route = route('/admin/system/group/form/standard/seek/reload');
        $selection_route = route('/admin/system/group/form/standard/seek/select');
        $self_class = (new \ReflectionClass(self::class))->getShortName();
        $window->setCalledClass($self_class);
        $window->setReloadRoute($pagination_route);
        $window->setSelectRoute($selection_route);
        $window->build();
        $window->onSetup($param);
        $window->show();
        TSession::setValue('window', self::$window);
    }

    /**
     * Remove program from session
     */
    public static function deleteProgram($param)
    {
        $programs = TSession::getValue('program_list');
        unset($programs[ $param['id'] ]);
        TSession::setValue('program_list', $programs);
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave($param)
    {
        try {
            // open a transaction with database 'permission'
            TTransaction::open('permission');

            $this->form->validate();

            // get the form data into an active record System_group
            $object = new SystemGroup;
            $object->fromArray($param);
            $object->store();
            $object->clearParts();
            
            $programs = TSession::getValue('program_list');
            if (!empty($programs)) {
                foreach ($programs as $program) {
                    $object->addSystemProgram(new SystemProgram($program['id']));
                }
            }
            
            $data = new stdClass;
            $data->id = $object->id;
            TForm::sendData('form_System_group', $data);
            
            TTransaction::close(); // close the transaction
            new TMessage('info', _t('Record saved')); // shows the success message
        } catch (Exception $e) { // in case of exception
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    public static function onClear()
    {
        Redirect::ajaxLoadPage(route('/admin/system/group/form'));
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    public function onEdit($param)
    {
        try {
            if (isset($param['key'])) {
                // get the parameter $key
                $key=$param['key'];
                
                // open a transaction with database 'permission'
                TTransaction::open('permission');
                
                // instantiates object System_group
                $object = new SystemGroup($key);
                
                $data = array();
                foreach ($object->getSystemPrograms() as $program) {
                    $data[$program->id] = $program->toArray();
                    
                    $item = new stdClass;
                    $item->id = $program->id;
                    $item->name = $program->name;
                    
                    $i = new TElement('i');
                    $i->{'class'} = 'fa fa-trash red';
                    $btn = new TElement('a');
                    $btn->{'onclick'} = "__adianti_ajax_exec('".route('/admin/system/group/form/program/delete/id/'.$program->id)."');$(this).closest('tr').remove();";
                    $btn->{'class'} = 'btn btn-default btn-sm';
                    $btn->add($i);
                    
                    $item->delete = $btn;
                    $tr = $this->program_list->addItem($item);
                    $tr->{'style'} = 'width: 100%;display: inline-table;';
                }
                
                // fill the form with the active record data
                $this->form->setData($object);
                
                // close the transaction
                TTransaction::close();
                
                TSession::setValue('program_list', $data);
            } else {
                $this->form->clear();
                TSession::setValue('program_list', null);
            }
        } catch (Exception $e) { // in case of exception
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Add a program
     */
    public static function onAddProgram($param)
    {
        try {
            $id = $param['program_id'];
            $program_list = TSession::getValue('program_list');
            
            if (!empty($id) and empty($program_list[$id])) {
                TTransaction::open('permission');
                $program = SystemProgram::find($id);
                $program_list[$id] = $program->toArray();
                TSession::setValue('program_list', $program_list);
                TTransaction::close();
                
                $i = new TElement('i');
                $i->{'class'} = 'fa fa-trash red';
                $btn = new TElement('a');
                $btn->{'onclick'} = "__adianti_ajax_exec(\'".route('/admin/system/group/form/program/delete/id/'.$id)."\');$(this).closest(\'tr\').remove();";
                $btn->{'class'} = 'btn btn-default btn-sm';
                $btn->add($i);
                
                $tr = new TTableRow;
                $tr->{'class'} = 'tdatagrid_row_odd';
                $tr->{'style'} = 'width: 100%;display: inline-table;';
                $cell = $tr->addCell($btn);
                $cell->{'style'}='text-align:center';
                $cell->{'class'}='tdatagrid_cell';
                $cell->{'width'} = '5%';
                $cell = $tr->addCell($program->id);
                $cell->{'class'}='tdatagrid_cell';
                $cell->{'width'} = '10%';
                $cell = $tr->addCell($program->name);
                $cell->{'class'}='tdatagrid_cell';
                $cell->{'width'} = '85%';
                
                TScript::create("tdatagrid_add_serialized_row('program_list', '$tr');");
                
                $data = new stdClass;
                $data->program_id = '';
                $data->program_name = '';
                TForm::sendData('form_System_group', $data);
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
}
