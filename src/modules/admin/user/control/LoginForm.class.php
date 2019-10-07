<?php

namespace Adianti\Base\Modules\Admin\User\Control;

use Adianti\Base\Lib\Control\TPage;
use Adianti\Base\Lib\Core\AdiantiApplicationConfig;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Lib\Widget\Form\TCombo;
use Adianti\Base\Lib\Widget\Form\TEntry;
use Adianti\Base\Lib\Widget\Form\TPassword;
use Adianti\Base\Lib\Wrapper\BootstrapFormBuilder;
use Adianti\Base\Modules\Admin\Program\Model\SystemProgram;
use Adianti\Base\Modules\Admin\User\Model\SystemUser;
use Adianti\Base\Modules\Log\Model\SystemAccessLog;
use App\Http\RouteInfo;
use App\Http\Router;
use Dvi\Adianti\Helpers\Redirect;
use Dvi\Adianti\Helpers\Reflection;
use Dvi\Component\Widget\Util\Action;
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
    protected $form; // form

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct($param)
    {
        parent::__construct();

        $ini = AdiantiApplicationConfig::get();

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

        $login->placeholder = _t('User');
        $password->placeholder = _t('Password');

        $login->autofocus = 'autofocus';

        $user = '<span style="float:left;margin-left:44px;height:35px;" class="login-avatar"><span class="glyphicon glyphicon-user"></span></span>';
        $locker = '<span style="float:left;margin-left:44px;height:35px;" class="login-avatar"><span class="glyphicon glyphicon-lock"></span></span>';
        $unit = '<span style="float:left;margin-left:44px;height:35px;" class="login-avatar"><span class="fa fa-university"></span></span>';

        $this->form->addFields([$user, $login]);
        $this->form->addFields([$locker, $password]);

        if (!empty($ini['general']['multiunit']) and $ini['general']['multiunit'] == '1') {
            $unit_id = new TCombo('unit_id');
            $unit_id->setSize('70%');
            $unit_id->style = 'height:35px;font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';
            $this->form->addFields([$unit, $unit_id]);
            $login->setExitAction(new Action(route('/login/exituser')));
        }

        $btn = $this->form->addAction(_t('Log in'), new Action(route('/onlogin'), 'POST'), '');
        $btn->class = 'btn btn-primary';
        $btn->style = 'height: 40px;width: 90%;display: block;margin: auto;font-size:17px;';

        $wrapper = new TElement('div');
        $wrapper->style = 'margin:auto; margin-top:100px;max-width:460px;';
        $wrapper->id = 'login-wrapper';
        $wrapper->add($this->form);

        // add the form to the page
        parent::add($wrapper);
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
        $ini = AdiantiApplicationConfig::get();

        try {
            TTransaction::open('permission');
            $data = (object)$param;

            if (empty($data->login)) {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', _t('Login')));
            }

            if (empty($data->password)) {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', _t('Password')));
            }

            if (!empty($ini['general']['multiunit']) and $ini['general']['multiunit'] == '1' and empty($data->unit_id)) {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', _t('Unit')));
            }

            $user = SystemUser::validate($data->login);

            if ($user) {
                if (!empty($ini['permission']['auth_service']) and class_exists($ini['permission']['auth_service'])) {
                    $service = $ini['permission']['auth_service'];
                    $service::authenticate($data->login, $data->password);
                } else {
                    SystemUser::authenticate($data->login, $data->password);
                }

                TSession::regenerate();
                $programs = $user->getPrograms();
                $programs['LoginForm'] = true;

                TSession::setValue('logged', true);
                TSession::setValue('login', $data->login);
                TSession::setValue('userid', $user->id);
                TSession::setValue('usergroupids', $user->getSystemUserGroupIds());
                TSession::setValue('userunitids', $user->getSystemUserUnitIds());
                TSession::setValue('username', $user->name);
                TSession::setValue('usermail', $user->email);
                TSession::setValue('frontpage', '');
                TSession::setValue('programs', $programs);
                TSession::setValue('profile_image', $user->profile_image);

                if (!empty($user->unit)) {
                    TSession::setValue('userunitid', $user->unit->id);
                }

                if (!empty($ini['general']['multiunit']) and $ini['general']['multiunit'] == '1' and !empty($data->unit_id)) {
                    TSession::setValue('userunitid', $data->unit_id);
                }

                $frontpage = $user->frontpage;
                SystemAccessLog::registerLogin();
                if ($frontpage instanceof SystemProgram and $frontpage->controller) {
                    TSession::setValue('frontpage', urlRoute($frontpage->route));
                    Redirect::ajaxGoTo($frontpage->route);
                } else {
                    TSession::setValue('frontpage', urlRoute('/admin/emptypage'));
                    Redirect::ajaxGoTo('/admin/emptypage');
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
                    //Todo $route = rota cuja classe é igual a $frontpage->controller e que não tenha metodo (terceiro parametro)
                    $program = $frontpage->controller;
                    $route = Router::routes()->map(function ($route_info, $route) use ($program) {
                        /**@var RouteInfo $route_info */
                        $program_short_name = (new \ReflectionClass($route_info->class()))->getShortName();
                        $method = $route_info->method();
                        if ($program_short_name == $program and empty($method)) {
                            return $route_info->route();
                        }
                    });
                    Redirect::ajaxGoTo($route); // reload
                } else {
                    Redirect::ajaxGoTo('/admin/emptypage');
                }
            }
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     *
     */
    public function onLoad($param)
    {
    }

    /**
     * Logout
     */
    public static function onLogout()
    {
        SystemAccessLog::registerLogout();
        TSession::freeSession();
        Redirect::redirectToRoute('/login');
    }
}
