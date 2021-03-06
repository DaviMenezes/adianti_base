<?php
namespace Adianti\Base\Modules\Admin\Control;

use Adianti\Base\Lib\Control\TPage;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Widget\Container\TPanelGroup;
use Adianti\Base\Lib\Widget\Datagrid\TDataGridAction;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Lib\Widget\Wrapper\TQuickGrid;
use Adianti\Base\Lib\Wrapper\BootstrapDatagridWrapper;
use Exception;
use Adianti\Base\App\Service\SystemPageService;

/**
 * SystemPageBatchUpdate
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemPageBatchUpdate extends TPage
{
    private $datagrid;
    
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
        
        if (TSession::getValue('login') !== 'admin') {
            new TMessage('error', _t('Permission denied'));
            return;
        }
        
        // creates one datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->width = '100%';
        
        // add the columns
        $this->datagrid->addQuickColumn(_t('Name'), 'name', 'left');
        $this->datagrid->addQuickColumn(_t('Controller'), 'controller', 'left');
        $this->datagrid->addQuickColumn(_t('Module'), 'module', 'left');
        
        $action1 = new TDataGridAction(urlRoute('/admin/system/pageupdate/edit'));
        $this->datagrid->addQuickAction('Download', $action1, 'controller', 'fa:download green');
        $action1->setUseButton(true);
        $action1->setButtonClass('btn btn-default');
        
        // creates the datagrid model
        $this->datagrid->createModel();
        
        try
        {
            $pages = SystemPageService::getPages();
            if ($pages)
            {
                foreach ($pages['data'] as $page)
                {
                    $page->controller = pathinfo($page->controller, PATHINFO_FILENAME);
                    $this->datagrid->addItem((object) $page);
                }
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        $panel = new TPanelGroup('Page Batch update');
        $panel->add($this->datagrid);
        
        parent::add($panel);
    }
}
