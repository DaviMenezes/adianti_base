<?php
namespace Adianti\Base\Modules\Admin\Unit\Control;

use Adianti\Base\Lib\Base\TStandardForm;
use Adianti\Base\Lib\Validator\TRequiredValidator;
use Adianti\Base\Lib\Widget\Container\TVBox;
use Adianti\Base\Lib\Widget\Form\TEntry;
use Adianti\Base\Lib\Widget\Form\TLabel;
use Adianti\Base\Lib\Widget\Util\TXMLBreadCrumb;
use Adianti\Base\Lib\Wrapper\BootstrapFormBuilder;
use Adianti\Base\Modules\Admin\Unit\Model\SystemUnit;
use Dvi\Adianti\Widget\Util\Action;

/**
 * SystemUnitForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemUnitForm extends TStandardForm
{
    protected $form; // form

    /**
     * Class constructor
     * Creates the page and the registration form
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('permission');              // defines the database
        $this->setActiveRecord(SystemUnit::class);     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_SystemUnit');
        $this->form->setFormTitle(_t('Unit'));
        
        // create the form fields
        $id = new TEntry('id');
        $name = new TEntry('name');
        
        // add the fields
        $this->form->addFields([new TLabel('Id')], [$id]);
        $this->form->addFields([new TLabel(_t('Name'))], [$name]);
        $id->setEditable(false);
        $id->setSize('30%');
        $name->setSize('70%');
        $name->addValidation(_t('Name'), new TRequiredValidator);
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new Action(route('/admin/system/unit/form/save'), 'POST'), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('Clear'), new Action(route('/admin/system/unit/form/clear')), 'fa:eraser red');
        $this->form->addActionLink(_t('Back'), new Action(urlRoute('/admin/system/unit')), 'fa:arrow-circle-o-left blue');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', '/admin/system/unit'));
        $container->add($this->form);
        
        parent::add($container);
    }
}
