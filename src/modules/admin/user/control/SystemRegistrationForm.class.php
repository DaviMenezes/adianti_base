<?php
namespace Adianti\Base\Modules\Admin\User\Control;

use Adianti\Base\Lib\Control\TPage;
use Adianti\Base\Lib\Core\AdiantiApplicationConfig;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Lib\Widget\Form\TEntry;
use Adianti\Base\Lib\Widget\Form\TLabel;
use Adianti\Base\Lib\Widget\Form\TPassword;
use Adianti\Base\Lib\Wrapper\BootstrapFormBuilder;
use Adianti\Base\Modules\Admin\Program\Model\SystemGroup;
use Adianti\Base\Modules\Admin\User\Model\SystemUser;
use Dvi\Adianti\Helpers\Redirect;
use Dvi\Component\Widget\Util\Action;
use Exception;

/**
 * SystemRegistrationForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemRegistrationForm extends TPage
{
    protected $form; // form
    protected $program_list;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_registration');
        $this->form->setFormTitle( _t('User registration') );
        
        // create the form fields
        $login      = new TEntry('login');
        $name       = new TEntry('name');
        $email      = new TEntry('email');
        $password   = new TPassword('password');
        $repassword = new TPassword('repassword');
        
        $this->form->addAction( _t('Save'),  new Action(route('/registry/save'), 'POST'), 'fa:floppy-o')->{'class'} = 'btn btn-sm btn-primary';
        $this->form->addAction( _t('Clear'), new Action(route('/registry/clear'), 'POST'), 'fa:eraser red' );
        //Todo check if necessary | $this->form->addActionLink( _t('Back'),  new Action(['LoginForm','onReload']), 'fa:arrow-circle-o-left blue' );
        
        // define the sizes
        $name->setSize('100%');
        $login->setSize('100%');
        $password->setSize('100%');
        $repassword->setSize('100%');
        $email->setSize('100%');
        
        $this->form->addFields( [new TLabel(_t('Login'), 'red')],    [$login] );
        $this->form->addFields( [new TLabel(_t('Name'), 'red')],     [$name] );
        $this->form->addFields( [new TLabel(_t('Email'), 'red')],    [$email] );
        $this->form->addFields( [new TLabel(_t('Password'), 'red')], [$password] );
        $this->form->addFields( [new TLabel(_t('Password confirmation'), 'red')], [$repassword] );
        
        // add the container to the page
        parent::add($this->form);
    }
    
    /**
     * Clear form
     */
    public function onClear()
    {
        $this->form->clear( true );
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public static function onSave($param)
    {
        try
        {
            $ini = AdiantiApplicationConfig::get();
            if ($ini['permission']['user_register'] !== '1')
            {
                throw new Exception( _t('The user registration is disabled') );
            }
            
            // open a transaction with database 'permission'
            TTransaction::open('permission');
            
            if( empty($param['login']) )
            {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', _t('Login')));
            }
            
            if( empty($param['name']) )
            {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', _t('Name')));
            }
            
            if( empty($param['email']) )
            {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', _t('Email')));
            }
            
            if( empty($param['password']) )
            {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', _t('Password')));
            }
            
            if( empty($param['repassword']) )
            {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', _t('Password confirmation')));
            }
            
            if (SystemUser::newFromLogin($param['login']) instanceof SystemUser)
            {
                throw new Exception(_t('An user with this login is already registered'));
            }
            
            if (SystemUser::newFromEmail($param['email']) instanceof SystemUser)
            {
                throw new Exception(_t('An user with this e-mail is already registered'));
            }
            
            if( $param['password'] !== $param['repassword'] )
            {
                throw new Exception(_t('The passwords do not match'));
            }
            
            $object = new SystemUser;
            $object->active = 'Y';
            $object->fromArray( $param );
            $object->password =  password_hash($object->password, PASSWORD_BCRYPT);
            $object->frontpage_id = $ini['permission']['default_screen'];
            $object->clearParts();
            $object->store();

            $default_groups = explode(',', $ini['permission']['default_groups']);
            if( count($default_groups) > 0 )
            {
                foreach( $default_groups as $group_id )
                {
                    $object->addSystemUserGroup( new SystemGroup($group_id) );
                }
            }
            
            TTransaction::close(); // close the transaction
            new TMessage('info', _t('Account created')); // shows the success message
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
