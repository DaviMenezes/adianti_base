<?php
namespace Adianti\Base\Modules\Communication\Control;

use Adianti\Base\Lib\Base\TStandardList;
use Adianti\Base\Lib\Database\TCriteria;
use Adianti\Base\Lib\Database\TFilter;
use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Base\TScript;
use Adianti\Base\Lib\Widget\Container\TPanelGroup;
use Adianti\Base\Lib\Widget\Container\TVBox;
use Adianti\Base\Lib\Widget\Datagrid\TDataGrid;
use Adianti\Base\Lib\Widget\Datagrid\TDataGridColumn;
use Adianti\Base\Lib\Widget\Datagrid\TPageNavigation;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Lib\Widget\Form\TDate;
use Adianti\Base\Lib\Widget\Form\TEntry;
use Adianti\Base\Lib\Widget\Form\TLabel;
use Adianti\Base\Lib\Widget\Util\TBreadCrumb;
use Adianti\Base\Lib\Widget\Util\TImage;
use Adianti\Base\Lib\Wrapper\BootstrapDatagridWrapper;
use Adianti\Base\Lib\Wrapper\BootstrapFormBuilder;
use Adianti\Base\Modules\Admin\User\Model\SystemUser;
use Adianti\Base\Modules\Communication\Model\SystemNotification;
use Dvi\Component\Widget\Util\Action;
use Exception;

/**
 * SystemNotificationList
 *
 * @version    1.0
 * @package    control
 * @subpackage communication
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemNotificationList extends TStandardList
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

        parent::setDatabase('communication');            // defines the database
        parent::setActiveRecord(SystemNotification::class);   // defines the active record
        parent::setDefaultOrder('id', 'desc');         // defines the default order
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_user_to_id', '=', TSession::getValue('userid')));
        parent::setCriteria($criteria); // define a standard filter

        parent::addFilterField('checked', 'like', 'checked'); // filterField, operator, formField
        parent::addFilterField('subject', 'like', 'subject'); // filterField, operator, formField
        parent::addFilterField('message', 'like', 'message'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_SystemNotification');
        $this->form->setFormTitle(_t('Notification'));
        
        // create the form fields
        $subject = new TEntry('subject');
        $message = new TEntry('message');

        // add the fields
        $this->form->addFields([new TLabel(_t('Subject'))], [$subject]);
        $this->form->addFields([new TLabel(_t('Message'))], [$message]);

        $subject->setSize('70%');
        $message->setSize('70%');
        
        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue('SystemNotification_filter_data'));
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new Action(route('/admin/system/notification/search'), 'POST'), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';

        // creates the datagrid columns
        $column_checked = new TDataGridColumn('action', _t('Action'), 'center', '50%');
        $column_message = new TDataGridColumn('message', _t('Message'), 'left', '50%');
        
        $column_message->setTransformer(function ($message, $object, $row) {
            if ($object->checked == 'Y') {
                $row->style = "color:gray";
            }
            try {
                TTransaction::open('permission');
                $user = SystemUser::find($object->system_user_id);
                $name = $user->name;
                TTransaction::close();
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage());
            }
            $str_message = '<i class="fa fa-calendar red"></i> '.TDate::date2br($object->dt_message);
            $str_message .=' '.$name . ' » '. $object->subject. '-'. $object->message;

            return $str_message;
        });
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_checked);
        $this->datagrid->addColumn($column_message);

        $order = new Action(urlRoute('/admin/system/notification/reload'));
        $order->setParameter('order', 'dt_message');
        $column_message->setAction($order);
        
        parent::setTransformer(array($this, 'onBeforeLoad'));
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation();
        $this->pageNavigation->setAction(new Action(urlRoute('/admin/system/notification/reload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(TBreadCrumb::create([_t('Notifications'), _t('List')]));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    /**
     * Iterate all objects before rendering
     * Create the check/uncheck buttons
     */
    public function onBeforeLoad($objects, $param)
    {
        foreach ($objects as $object) {
            $button = new TElement('a');
            $button->generator = 'adianti';
            $button->class = 'btn btn-default';
            //$button->style="width:160px";
            
            if ($object->checked == 'Y') {
                $button->href = urlRoute('/admin/system/notification/list/uncheck/'.$object->id);
                $button->add(new TImage('fa:archive gray'));
                $button->add(TElement::tag('span', _t('Check as unread'), array('style' =>'color:gray' )));
            } else {
                $button->href = urlRoute('/admin/system/notification/form/exec/'.$object->id);

                $button->add(new TImage('fa:' . substr($object->icon, 6)));
                $button->add(TElement::tag('span', $object->action_label));
            }
            
            $object->action = $button;
        }
    }
    
    /**
     * Check message as read
     */
    public function onCheck($param)
    {
        try {
            TTransaction::open('communication');
            
            $message = SystemNotification::find($param['id']);
            if ($message) {
                if ($message->system_user_to_id == TSession::getValue('userid')) {
                    $message->checked = 'Y';
                    $message->store();
                    TScript::create('update_notifications_menu()');
                } else {
                    throw new Exception(_t('Permission denied'));
                }
            }
            TTransaction::close();
            
            parent::onReload($param);
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Check message as unread
     */
    public function onUncheck($param)
    {
        try {
            TTransaction::open('communication');
            
            $message = SystemNotification::find($param['id']);
            if ($message) {
                if ($message->system_user_to_id == TSession::getValue('userid')) {
                    $message->checked = 'N';
                    $message->store();
                    TScript::create('update_notifications_menu()');
                } else {
                    throw new Exception(_t('Permission denied'));
                }
            }
            TTransaction::close();
            
            parent::onReload($param);
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
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
        $this->route = urlRoute('/admin/system/notification');
    }
}
