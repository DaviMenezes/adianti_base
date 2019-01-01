<?php
namespace Adianti\Base\Modules\Log\Control;

use Adianti\Base\Lib\Base\TStandardList;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Widget\Container\TPanelGroup;
use Adianti\Base\Lib\Widget\Container\TVBox;
use Adianti\Base\Lib\Widget\Datagrid\TPageNavigation;
use Adianti\Base\Lib\Widget\Form\TEntry;
use Adianti\Base\Lib\Widget\Form\TLabel;
use Adianti\Base\Lib\Widget\Util\TXMLBreadCrumb;
use Adianti\Base\Lib\Widget\Wrapper\TQuickGrid;
use Adianti\Base\Lib\Wrapper\BootstrapDatagridWrapper;
use Adianti\Base\Lib\Wrapper\BootstrapFormBuilder;
use Adianti\Base\Modules\Log\Model\SystemSqlLog;
use Dvi\Adianti\Widget\Util\Action;

/**
 * SystemSqlLogList
 *
 * @version    1.0
 * @package    control
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemSqlLogList extends TStandardList
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();

        $this->setRoute();

        parent::setDatabase('log');            // defines the database
        parent::setActiveRecord(SystemSqlLog::class);   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        parent::addFilterField('login', 'like'); // add a filter field
        parent::addFilterField('database_name', 'like'); // add a filter field
        parent::addFilterField('sql_command', 'like'); // add a filter field
        parent::setLimit(20);
        
        // creates the form, with a table inside
        $this->form = new BootstrapFormBuilder('form_search_SystemSqlLog');
        $this->form->setFormTitle('SQL Log');
        
        // create the form fields
        $login       = new TEntry('login');
        $database    = new TEntry('database_name');
        $sql         = new TEntry('sql_command');


        // add the fields
        $this->form->addFields([new TLabel(_t('Login'))], [$login]);
        $this->form->addFields([new TLabel(_t('Database'))], [$database]);
        $this->form->addFields([new TLabel('SQL')], [$sql]);

        $login->setSize('70%');
        $database->setSize('70%');
        $sql->setSize('70%');
        
        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue('SystemSqlLog_filter_data'));
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new Action(route('/admin/system/log/sql/search'), 'POST'), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->disableDefaultClick();
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        
        // creates the datagrid columns
        $this->datagrid->addQuickColumn('ID', 'id', 'center', 50, new Action(urlRoute('/admin/system/log/sql')), array('order', 'id'));
        $this->datagrid->addQuickColumn(_t('Date'), 'logdate', 'center', null, new Action(urlRoute('/admin/system/log/sql')), array('order', 'logdate'));
        $this->datagrid->addQuickColumn(_t('Login'), 'login', 'center', null, new Action(urlRoute('/admin/system/log/sql')), array('order', 'login'));
        $this->datagrid->addQuickColumn(_t('Database'), 'database_name', 'left', null, new Action(urlRoute('/admin/system/log/sql')), array('order', 'database_name'));
        $this->datagrid->addQuickColumn('SQL', 'sql_command', 'left', null);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new Action(urlRoute('/admin/system/log/sql/reload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        $container = new TVBox;
        $container->style = 'width: 97%';
        $container->add(new TXMLBreadCrumb('menu.xml', '/admin/system/log/sql'));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
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
        $this->route = urlRoute('/admin/system/log/sql');
    }
}
