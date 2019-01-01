<?php
namespace Adianti\Base\Modules\Communication\Control;

use Adianti\Base\Lib\Control\TPage;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Validator\TRequiredValidator;
use Adianti\Base\Lib\Widget\Container\TVBox;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Lib\Widget\Form\TFile;
use Adianti\Base\Lib\Widget\Form\THidden;
use Adianti\Base\Lib\Widget\Form\TLabel;
use Adianti\Base\Lib\Widget\Util\TXMLBreadCrumb;
use Adianti\Base\Lib\Wrapper\BootstrapFormBuilder;
use Dvi\Adianti\Helpers\Redirect;
use Dvi\Adianti\Widget\Util\Action;
use Exception;
use stdClass;

/**
 * SystemDocumentUploadForm
 *
 * @version    1.0
 * @package    control
 * @subpackage communication
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemDocumentUploadForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct($param)
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_SystemUploadDocument');
        $this->form->setFormTitle(_t('Send document'));

        // create the form fields
        $id = new THidden('id');
        $filename = new TFile('filename');
        $filename->setService(urlRoute('/admin/system/service/document/upload'));

        $row = $this->form->addFields([new TLabel('ID')], [$id]);
        $row->style = 'display:none';
        $this->form->addFields([new TLabel(_t('File'))], [$filename]);
        $filename->setSize('80%');
        $filename->addValidation(_t('File'), new TRequiredValidator);
        
        $btn = $this->form->addAction(_t('Next'), new Action(route('/admin/system/document/upload/next'), 'POST'), 'fa:arrow-circle-o-right');
        $btn->class = 'btn btn-sm btn-primary';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', '/admin/system/document/upload'));
        $container->add($this->form);
        
        parent::add($container);
    }

    public function onNew()
    {
    }
    
    public function onEdit($param)
    {
        if ($param['id']) {
            $obj = new stdClass;
            $obj->id = $param['id'];
            $this->form->setData($obj);
        }
    }
    
    /**
     * Save form data
     * @param $param Request
     */
    public function onNext($param)
    {
        try {
            $data = $this->form->getData(); // get form data as array
            $this->form->validate(); // validate form data
            TSession::setValue('system_document_upload_file', $data->filename);
            
            if ($data->id) {
                $param['key'] = $param['id'];
                $param['hasfile'] = '1';
                Redirect::ajaxLoadPage('/admin/system/document/form/edit', $param);
            } else {
                $param['hasfile'] = '1';
                Redirect::ajaxLoadPage('/admin/system/document/form');
            }
        } catch (Exception $e) { // in case of exception
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData($this->form->getData()); // keep form data
        }
    }
}
