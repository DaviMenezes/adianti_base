<?php
namespace Adianti\Base\Modules\Admin\Control;

use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Control\TPage;
use Adianti\Base\Lib\Core\AdiantiApplicationConfig;
use Adianti\Base\Lib\Core\AdiantiCoreApplication;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Core\TApplication;
use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Validator\TEmailValidator;
use Adianti\Base\Lib\Validator\TRequiredValidator;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Dialog\TAlert;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Lib\Widget\Form\TCombo;
use Adianti\Base\Lib\Widget\Form\TEntry;
use Adianti\Base\Lib\Widget\Form\TForm;
use Adianti\Base\Lib\Widget\Form\TPassword;
use Adianti\Base\Lib\Wrapper\BootstrapFormBuilder;
use Adianti\Base\Modules\Admin\Model\SystemPreference;
use Adianti\Base\Modules\Admin\Model\SystemProgram;
use Adianti\Base\Modules\Admin\Model\SystemUser;
use Adianti\Base\Modules\Log\Model\SystemAccessLog;
use App\Control\Pessoa\ResetPassword;
use App\Model\Pessoa\EntityQuery;
use Dvi\Adianti\Database\DTransaction;
use Dvi\Adianti\Helpers\DviMail;
use Exception;

/**
 * LoginForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class LoginForm extends TPage
{
    protected $form;
    protected $wrapper;

    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct($param)
    {
        parent::__construct();

        $this->createLoginForm();
        
        parent::add($this->wrapper);
    }

    protected static function setUserSessions($data, $user, $programs)
    {
        TSession::setValue('logged', true);
        TSession::setValue('login', $user->login);
        TSession::setValue('userid', $user->id);
        TSession::setValue('usergroupids', $user->getSystemUserGroupIds());
        TSession::setValue('userunitids', $user->getSystemUserUnitIds());
        TSession::setValue('username', $user->name);
        TSession::setValue('usermail', $user->email);
        TSession::setValue('frontpage', '');
        TSession::setValue('programs', $programs);
    }

    /**
     * user exit action
     * Populate unit combo
     */
    public static function onExitUser($param)
    {
        try {
            TTransaction::open('permission');
            
            $user = SystemUser::newFromLogin($param['login']);
            if ($user instanceof SystemUser) {
                $units = $user->getSystemUserUnits();
                $options = [];
                
                if ($units) {
                    foreach ($units as $unit) {
                        $options[$unit->id] = $unit->name;
                    }
                }
                TCombo::reload('form_login', 'unit_id', $options);
            }
            
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Authenticate the User
     */
    public static function onLogin($param)
    {
        $ini  = AdiantiApplicationConfig::get();
        
        try {
            TTransaction::open('permission');
            $data = (object) $param;
            
            if (empty($data->login)) {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', _t('Login')));
            }
            
            if (empty($data->password)) {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', _t('Password')));
            }
            
            $user = SystemUser::authenticate($data->login, $data->password);
            if ($user) {
                TSession::regenerate();
                $programs = $user->getPrograms();
                $programs['LoginForm'] = true;

                self::setUserSessions($data, $user, $programs);

                if (!empty($user->unit)) {
                    TSession::setValue('userunitid', $user->unit->id);
                }
                
                if (!empty($ini['general']['multiunit']) and $ini['general']['multiunit'] == '1' and !empty($data->unit_id)) {
                    TSession::setValue('userunitid', $data->unit_id);
                }
                
                $frontpage = $user->frontpage;
                SystemAccessLog::registerLogin();
                if ($frontpage instanceof SystemProgram and $frontpage->controller) {
                    AdiantiCoreApplication::gotoPage($frontpage->controller); // reload
                    TSession::setValue('frontpage', $frontpage->controller);
                } else {
                    AdiantiCoreApplication::gotoPage('EmptyPage'); // reload
                    TSession::setValue('frontpage', 'EmptyPage');
                }
            }
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TSession::setValue('logged', false);
            TTransaction::rollback();
        }
    }
    
    /**
     * Reload permissions
     */
    public static function reloadPermissions()
    {
        try {
            TTransaction::open('permission');
            $user = SystemUser::newFromLogin(TSession::getValue('login'));
            if ($user) {
                $programs = $user->getPrograms();
                $programs['LoginForm'] = true;
                TSession::setValue('programs', $programs);
                
                $frontpage = $user->frontpage;
                if ($frontpage instanceof SystemProgram and $frontpage->controller) {
                    TApplication::gotoPage($frontpage->controller); // reload
                } else {
                    TApplication::gotoPage('EmptyPage'); // reload
                }
            }
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Logout
     */
    public static function onLogout()
    {
        SystemAccessLog::registerLogout();
        TSession::freeSession();
        AdiantiCoreApplication::gotoPage('LoginForm', '');
    }

    protected function createLoginForm()
    {
        $this->style = 'clear:both';
        // creates the form
        $this->form = new BootstrapFormBuilder('form_login');
        $this->form->setFormTitle('LOG IN');

        // create the form fields
        $login = new TEntry('login');
        $password = new TPassword('password');

        // define the sizes
        $login->setSize('70%', 40);
        $password->setSize('70%', 40);

        $login->style = 'height:35px; font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';
        $password->style = 'height:35px;font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';

        $login->placeholder = 'Login';
        $password->placeholder = _t('Password');

        $user = '<span style="float:left;margin-left:44px;height:35px;" class="login-avatar"><span class="glyphicon glyphicon-user"></span></span>';
        $locker = '<span style="float:left;margin-left:44px;height:35px;" class="login-avatar"><span class="glyphicon glyphicon-lock"></span></span>';
        $unit = '<span style="float:left;margin-left:44px;height:35px;" class="login-avatar"><span class="fa fa-university"></span></span>';

        $this->form->addFields([$user, $login]);
        $this->form->addFields([$locker, $password]);

        $ini = AdiantiApplicationConfig::get();
        if (!empty($ini['general']['multiunit']) and $ini['general']['multiunit'] == '1') {
            $unit_id = new TCombo('unit_id');
            $unit_id->setSize('70%');
            $unit_id->style = 'height:35px;font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';
            $this->form->addFields([$unit, $unit_id]);
            $login->setExitAction(new TAction([$this, 'onExitUser']));
        }

        $btn_login = $this->form->addAction(_t('Log in'), new TAction(array($this, 'onLogin')), '');
        $btn_login->class = 'btn btn-primary';
        $btn_login->style = 'height: 40px;width: 100%;margin: auto;font-size:17px;';

        $this->wrapper = new TElement('div');
        $this->wrapper->style = 'margin:auto; margin-top:100px;max-width:460px;';
        $this->wrapper->id = 'login-wrapper';
        $this->wrapper->add($this->form);
    }
}
