<?php
namespace Adianti\Base\Modules\Admin\Program\Control;

use Adianti\Base\Lib\Base\TStandardList;
use Adianti\Base\Lib\Core\AdiantiApplicationConfig;
use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Widget\Container\TPanelGroup;
use Adianti\Base\Lib\Widget\Container\TVBox;
use Adianti\Base\Lib\Widget\Datagrid\TDataGrid;
use Adianti\Base\Lib\Widget\Datagrid\TDataGridAction;
use Adianti\Base\Lib\Widget\Datagrid\TDataGridActionGroup;
use Adianti\Base\Lib\Widget\Datagrid\TDataGridColumn;
use Adianti\Base\Lib\Widget\Datagrid\TPageNavigation;
use Adianti\Base\Lib\Widget\Dialog\TInputDialog;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Lib\Widget\Form\TCombo;
use Adianti\Base\Lib\Widget\Form\TEntry;
use Adianti\Base\Lib\Widget\Form\TIcon;
use Adianti\Base\Lib\Widget\Form\TLabel;
use Adianti\Base\Lib\Widget\Util\TXMLBreadCrumb;
use Adianti\Base\Lib\Widget\Wrapper\TQuickForm;
use Adianti\Base\Lib\Wrapper\BootstrapDatagridWrapper;
use Adianti\Base\Lib\Wrapper\BootstrapFormBuilder;
use Adianti\Base\Modules\Admin\Program\Model\SystemProgram;
use Adianti\Base\Widget\Menu\TMenuParser;
use App\Http\Request;
use App\Http\RouteInfo;
use App\Http\Router;
use Dvi\Adianti\Database\Transaction;
use Dvi\Adianti\Helpers\Redirect;
use Dvi\Adianti\Helpers\Reflection;
use Dvi\Adianti\Widget\Util\Action;
use Exception;

/**
 * SystemProgramList
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemProgramList extends TStandardList
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $transformCallback;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setRoute();

        parent::setDatabase('default');            // defines the database
        parent::setActiveRecord(SystemProgram::class);   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        // parent::setCriteria($criteria) // define a standard filter

        parent::addFilterField('id', '=', 'id'); // filterField, operator, formField
        parent::addFilterField('name', 'like', 'name'); // filterField, operator, formField
        parent::addFilterField('controller', 'like', 'controller'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_SystemProgram');
        $this->form->setFormTitle(_t('Programs'));
        

        // create the form fields
        $name = new TEntry('name');
        $controller = new TEntry('controller');

        // add the fields
        $this->form->addFields([new TLabel(_t('Name'))], [$name]);
        $this->form->addFields([new TLabel(_t('Controller'))], [$controller]);
        $name->setSize('70%');
        $controller->setSize('70%');
        
        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue('SystemProgram_filter_data'));
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new Action(route('/admin/system/program/list/search'), 'POST'), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new Action(urlRoute('/admin/system/program/form')), 'bs:plus-sign green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center', 50);
        $column_controller = new TDataGridColumn('controller', _t('Controller'), 'left');
        $column_name = new TDataGridColumn('name', _t('Name'), 'left');
        $column_menu = new TDataGridColumn('route', _t('Menu path'), 'left');

        $column_menu->setTransformer(function ($value, $object, $row) {
            $menuparser = new TMenuParser('menu.xml');
            $paths = $menuparser->getPath($value);
            
            if ($paths) {
                return implode(' &raquo; ', $paths);
            }
        });

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_controller);
        $this->datagrid->addColumn($column_name);
        $this->datagrid->addColumn($column_menu);


        // creates the datagrid column actions
        $order_id = new Action($this->getReloadRoute());
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);
        
        $order_name = new Action($this->getReloadRoute());
        $order_name->setParameter('order', 'name');
        $column_name->setAction($order_name);
        
        $order_controller = new Action($this->getReloadRoute());
        $order_controller->setParameter('order', 'controller');
        $column_controller->setAction($order_controller);
        
        // create EDIT action
        $action_edit = new TDataGridAction(urlRoute('admin/system/program/edit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(urlRoute('/admin/system/program/list/delete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('fa:trash-o red fa-lg');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        
        // create EXECUTE action
        $action_ope = new TDataGridAction(urlRoute('/admin/system/program/list/open'));
        $action_ope->setButtonClass('btn btn-default');
        $action_ope->setLabel(_t('Open'));
        $action_ope->setImage('fa:folder-open-o green fa-lg');
        $action_ope->setField('controller');
        $this->datagrid->addAction($action_ope);

        // create ADD MENU action
        $action_add_menu = new TDataGridAction(urlRoute('/admin/system/program/list/menu/add'));
        $action_add_menu->setDisplayCondition([$this, 'displayAddMenu']);
        $action_add_menu->setButtonClass('btn btn-default');
        $action_add_menu->setLabel(_t('Add to menu'));
        $action_add_menu->setImage('fa:plus green fa-lg');
        $action_add_menu->setFields(['id']);
        
        // create DEL MENU action
        $action_del_menu = new TDataGridAction(urlRoute('/admin/system/program/list/menu/delete'));
        $action_del_menu->setDisplayCondition([$this, 'displayDelMenu']);
        $action_del_menu->setButtonClass('btn btn-default');
        $action_del_menu->setLabel(_t('Remove from menu'));
        $action_del_menu->setImage('fa:times red fa-lg');
        $action_del_menu->setField('id');
        
        $action_group = new TDataGridActionGroup('', 'fa:list');
        $action_group->addHeader('Menu');
        $action_group->addAction($action_add_menu);
        $action_group->addAction($action_del_menu);
        
        // add the actions to the datagrid
        $this->datagrid->addActionGroup($action_group);
        
        $ini = AdiantiApplicationConfig::get();
        
        if ((TSession::getValue('login') == 'admin') && isset($ini['general']['token'])) {
            $action_edit_page = new TDataGridAction(urlRoute('/admin/system/pageservice/edit'));
            $action_edit_page->setButtonClass('btn btn-default');
            $action_edit_page->setLabel(_t('Edit page'));
            $action_edit_page->setImage('fa:external-link green fa-lg');
            $action_edit_page->setField('controller');
            $action_edit_page->setDisplayCondition([$this, 'displayBuilderActions']);
            $this->datagrid->addAction($action_edit_page);
            
            $action_get_page = new TDataGridAction(urlRoute('/admin/system/pageupdate/edit'));
            $action_get_page->setButtonClass('btn btn-default');
            $action_get_page->setLabel(_t('Update page'));
            $action_get_page->setImage('fa:download purple fa-lg');
            $action_get_page->setField('controller');
            $action_get_page->setDisplayCondition([$this, 'displayBuilderActions']);
            $this->datagrid->addAction($action_get_page);
        }
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new Action($this->getReloadRoute()));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', route('/admin/system/program')));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    /**
     * Display condition for add to menu option
     */
    public function displayAddMenu($object)
    {
        $menuparser = new TMenuParser('menu.xml');
        $route = $object->route;
        $result = count((array) $menuparser->getPath($route)) == 0;
        return $result;
    }
    
    /**
     * Display condition for del from menu option
     */
    public function displayDelMenu($object)
    {
        $menuparser = new TMenuParser('menu.xml');
        return count((array) $menuparser->getPath($object->route)) > 0;
    }
    
    /**
     * Open controller
     */
    public function onOpen($param)
    {
        try {
            /**@var RouteInfo $route_info*/
            $route_info = Router::routes()->first(function ($route_info, $route) use ($param) {
                return Reflection::shortName($route_info->class()) == $param['controller'] and empty($route_info->method());
            });
            if (!$route_info) {
                throw new Exception('Rota não encontrada');
            }
            Redirect::ajaxLoadPage(urlRoute($route_info->fullRoute()));
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Add item on menu
     */
    public function onAddMenu($param)
    {
        try {
            Transaction::open();
            
            $modules = (new TMenuParser('menu.xml'))->getModules();
            
            $form = new TQuickForm('input_form');
            $form->class = 'input_form';
            $form->style = 'padding:20px';
            
            $module = new TCombo('module');
            $module->addItems($modules);
            $module->enableSearch();

            $program = SystemProgram::find($param['id']);
            $name = new TEntry('name');
            
            $icon = new TIcon('icon');
            $icon->setValue('fa-circle-o');
            
            $form->addQuickField(_t('Module'), $module);
            $form->addQuickField(_t('Name'), $name);
            $form->addQuickField(_t('Icon'), $icon);
            
            $module->setSize('70%');
            $icon->setSize('70%');
            $name->setSize('70%');
            
            $name->setValue($program->name);
            
            $action = new Action(urlRoute('/admin/system/program/list/additemmenu'), 'POST');
            $action->setParameters($param);
            
            $form->addQuickAction(_t('Add'), $action, 'fa:save green');
            new TInputDialog(_t('Add to menu'), $form);
            
            Transaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Add item at menu
     */
    public function addItemMenu($param)
    {
        try {
            Transaction::open();

            $program = SystemProgram::find($param['id']);

            $menu = new TMenuParser('menu.xml');
            $menu->appendPage($param['module'], $param['name'], $program->route, str_replace('fa-', 'fa:', $param['icon'] . ' fa-fw'));
            
            $posaction = new Action(urlRoute('/admin/system/program'));
            new TMessage('info', _t('Item added to menu'), $posaction);
            Transaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Remove item from menu
     */
    public function onDelMenu($param)
    {
        try {
            TTransaction::open('permission');

            $program = SystemProgram::find($param['id']);
            $menu = new TMenuParser('menu.xml');
            $menu->removePage($program->route);
            
            $posaction = new Action(urlRoute('/admin/system/program/reloadUpdateMenu'));
            new TMessage('info', _t('Item removed from menu'), $posaction);

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public function reloadUpdateMenu()
    {
        Redirect::ajaxGoTo(route('/admin/system/program'));
    }

    /**
     * Display condition
     */
    public function displayBuilderActions($object)
    {
        return ((strpos($object->controller, 'System') === false) and !in_array($object->controller, ['CommonPage', 'WelcomeView']));
    }

    /**
     * Dvi setRoute
     * set a route base used in actions
     * <code>
     * $this->route = '/admin/route';
     * </code>
     */
    public function setRoute()
    {
        $this->route = urlRoute('/admin/system/program/list');
    }

    protected function getReloadRoute(): string
    {
        return urlRoute('/admin/system/program/list/reload');
    }
}
