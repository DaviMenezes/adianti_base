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
use Adianti\Base\Modules\Log\Model\SystemAccessLog;

/**
 * SystemAccessLogList
 *
 * @version    1.0
 * @package    control
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemAccessLogList extends TStandardList
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
        parent::setActiveRecord(SystemAccessLog::class);   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        parent::addFilterField('login', 'like'); // add a filter field
        parent::setLimit(20);
        
        // creates the form, with a table inside
        $this->form = new BootstrapFormBuilder('form_search_SystemAccessLog');
        $this->form->setFormTitle('Access Log');
        
        // create the form fields
        $login = new TEntry('login');

        // add the fields
        $this->form->addFields([new TLabel(_t('Login'))], [$login]);
        $login->setSize('70%');
        
        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue('SystemAccessLog_filter_data'));
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new Action(route('/admin/system/log/access/search'), 'POST'), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        //Todo variaveis nao usadas
        $id = $this->datagrid->addQuickColumn('id', 'id', 'left');
        $sessionid = $this->datagrid->addQuickColumn('sessionid', 'sessionid', 'left');
        $login = $this->datagrid->addQuickColumn(_t('Login'), 'login', 'left');
        $login_time = $this->datagrid->addQuickColumn('login_time', 'login_time', 'left');
        $logout_time = $this->datagrid->addQuickColumn('logout_time', 'logout_time', 'left');

        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new Action(urlRoute('/admin/system/log/access/reload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        $container = new TVBox;
        $container->style = 'width: 97%';
        $container->add(new TXMLBreadCrumb('menu.xml', '/admin/system/log/access'));
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
        $this->route = urlRoute('/admin/system/log/access');
    }
}
