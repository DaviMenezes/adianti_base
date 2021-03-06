<?php
namespace Adianti\Base\Modules\Admin\Control;

use Adianti\Base\App\Lib\Util\MailService;
use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Control\TWindow;
use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Validator\TRequiredValidator;
use Adianti\Base\Lib\Widget\Container\TPanelGroup;
use Adianti\Base\Lib\Widget\Container\TVBox;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Lib\Widget\Form\TEntry;
use Adianti\Base\Lib\Widget\Form\TText;
use Adianti\Base\Lib\Widget\Wrapper\TQuickForm;
use Adianti\Base\Lib\Wrapper\BootstrapFormWrapper;
use Adianti\Base\Modules\Admin\Model\SystemPreference;
use Dvi\Component\Widget\Util\Action;
use Exception;

/**
 * SystemSupportForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemSupportForm extends TWindow
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct()
    {
        parent::__construct();
        parent::setSize(0.8, null);
        parent::setTitle(_t('Open ticket'));
        parent::setProperty('class', 'window_modal');
        
        // creates the form
        $this->form = new BootstrapFormWrapper(new TQuickForm('form_SystemMessage'));
        $this->form->style = 'display: table;width:100%'; // change style
        
        // define the form title
        $this->form->setFormTitle(_t('Ticket'));
        
        // create the form fields
        $subject = new TEntry('subject');
        $message = new TText('message');

        // add the fields
        $this->form->addQuickField(_t('Title'), $subject, '90%', new TRequiredValidator);
        $this->form->addQuickField(_t('Message'), $message, '90%', new TRequiredValidator);
        $message->setSize('90%', '100');
        
        if (!empty($id)) {
            $id->setEditable(false);
        }
        
        // create the form actions
        $btn = $this->form->addQuickAction(_t('Send'), new Action(route('/admin/system/support/send'), 'POST'), 'fa:envelope-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('Clear form'), new Action(route('/admin/system/support/clear'), 'POST'), 'fa:eraser red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(TPanelGroup::pack('', $this->form));
        
        parent::add($container);
    }
    
    public function onClear($param)
    {
    
    }
    
    public function onSend($param)
    {
        try {
            // get the form data
            $data = $this->form->getData();
            // validate data
            $this->form->validate();
            
            TTransaction::open('permission');
            $preferences = SystemPreference::getAllPreferences();
            TTransaction::close();
            
            MailService::send( trim($preferences['mail_support']), $data->subject, $data->message );
            
            // shows the success message
            new TMessage('info', _t('Message sent successfully'));
        } catch (Exception $e) { // in case of exception
            // get the form data
            $object = $this->form->getData();
            
            // fill the form with the active record data
            $this->form->setData($object);
            
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}
