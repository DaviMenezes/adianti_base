<?php
namespace Adianti\Base\Modules\Admin\User\Control;

use Adianti\Base\Lib\Control\TPage;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Validator\TRequiredValidator;
use Adianti\Base\Lib\Widget\Container\TPanelGroup;
use Adianti\Base\Lib\Widget\Container\TVBox;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Lib\Widget\Form\TEntry;
use Adianti\Base\Lib\Widget\Form\TFile;
use Adianti\Base\Lib\Widget\Form\TPassword;
use Adianti\Base\Lib\Widget\Wrapper\TQuickForm;
use Adianti\Base\Lib\Wrapper\BootstrapFormWrapper;
use Adianti\Base\Modules\Admin\User\Model\SystemUser;
use Dvi\Support\Http\Request;
use Dvi\Component\Widget\Container\VBox;
use Dvi\Component\Widget\Util\Action;
use Exception;

/**
 * SystemProfileForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemProfileForm extends TPage
{
    private $form;
    /**@var SystemUser*/
    private $user;

    public function __construct(Request $request)
    {
        parent::__construct();

        $this->loadUser();
        
        $this->form = new BootstrapFormWrapper(new TQuickForm);
        $this->form->setFormTitle(_t('Profile'));
        
        $name  = new TEntry('name');
        $login = new TEntry('login');
        $email = new TEntry('email');
        $photo = new TFile('photo');
        $photo->setService(urlRoute('/admin/system/profile/image/upload'));

        $password1 = new TPassword('password1');
        $password2 = new TPassword('password2');
        $login->setEditable(false);
        //        $photo->setAllowedExtensions( ['jpg', 'png'] );
        
        $this->form->addQuickField(_t('Name'), $name, '80%', new TRequiredValidator);
        $this->form->addQuickField(_t('Login'), $login, '80%', new TRequiredValidator);
        $this->form->addQuickField(_t('Email'), $email, '80%', new TRequiredValidator);
        $this->form->addQuickField(_t('File'), $photo, '80%');
        $this->form->addQuickField(_t('Password'), $password1, '80%');
        $this->form->addQuickField(_t('Password confirmation'), $password2, '80%');

        $btn = $this->form->addQuickAction(_t('Save'), new Action(route('/admin/system/profile/form/save'), 'POST'), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        $panel = new TPanelGroup(_t('Profile'));

        $image = $this->user->profile_image;
        if (!$image) {
            $image = '/app/templates/theme3/img/avatar5.png';
        }
        $panel_content = new VBox();
        $panel_content->add($this->form);

        $box_image = new VBox();
        $box_image->style = 'text-align:center';
        $box_image->add('<hr>');
        $box_image->add("<img src='".urlRoute($image)."' width='100px'>");
        $box_image->add('<br>');

        if (!empty($this->user->profile_image)) {
            $box_image->add('<a href="' . urlRoute('/admin/system/user/image/remove') . '" class="btn btn-danger">
                <i class="fa fa-trash" aria-hidden="true"></i> Remover imagem
            </a>');

        }
        $panel_content->add($box_image);
        $panel->add($panel_content);

        $container = new TVBox();
        $container->add($panel);

        $container->style = 'width:90%';
        parent::add($container);
    }

    public function onEdit($param)
    {
        $this->form->setData($this->user);
    }
    
    public function onSave($param)
    {
        try {
            $this->form->validate();
            
            $object = $this->form->getData();
            
            TTransaction::open('permission');
            $user = SystemUser::newFromLogin(TSession::getValue('login'));
            $user->name = $object->name;
            $user->email = $object->email;

            $this->validatePassword($object, $user);

            if ($object->photo) {
                $source_file   = 'files/users/'.TSession::getValue('userid').'/'.$object->photo;
                $user->profile_image = $source_file;
            }
            $user->store();
            $this->form->setData($object);
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
            
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    private function validatePassword($object, $user)
    {
        if (empty($object->password1) and empty($object->password2)) {
            return true;
        } else {
            $user->password = password_hash($object->password1, PASSWORD_BCRYPT);
        }

        if (empty($object->password1) or empty($object->password2)) {
            throw new Exception('Informe a senha');
        }

        if ($object->password1 != $object->password2) {
            throw new Exception(_t('The passwords do not match'));
        }
    }

    private function loadUser(): void
    {
        try {
            TTransaction::open('permission');
            $this->user = SystemUser::newFromLogin(TSession::getValue('login'));

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
}
