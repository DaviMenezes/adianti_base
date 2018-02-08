<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Base\TScript;
use Exception;

/**
 * Color Widget
 *
 * @version    5.0
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TColor extends TEntry implements AdiantiWidgetInterface
{
    private $mask;
    protected $formName;
    protected $name;
    protected $id;
    protected $size;
    protected $changeFunction;
    protected $changeAction;
    
    /**
     * Class Constructor
     * @param $name Name of the widget
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->id = 'tcolor_'.mt_rand(1000000000, 1999999999);
        $this->tag->{'widget'} = 'tcolor';
    }
    
    /**
     * Enable the field
     * @param $form_name Form name
     * @param $field Field name
     */
    public static function enableField($form_name, $field)
    {
        TScript::create(" tcolor_enable_field('{$form_name}', '{$field}'); ");
    }
    
    /**
     * Disable the field
     * @param $form_name Form name
     * @param $field Field name
     */
    public static function disableField($form_name, $field)
    {
        TScript::create(" tcolor_disable_field('{$form_name}', '{$field}'); ");
    }
    
    /**
     * Set change function
     */
    public function setChangeFunction($function)
    {
        $this->changeFunction = $function;
    }
    
    /**
     * Define the action to be executed when the user changes the content
     * @param $action TAction object
     */
    public function setChangeAction(TAction $action)
    {
        $this->changeAction = $action;
    }
    
    /**
     * Shows the widget at the screen
     */
    public function show()
    {
        $wrapper = new TElement('div');
        $wrapper->{'class'} = 'input-group color-div colorpicker-component';
        $wrapper->{'style'} = 'float:inherit';
        
        $span = new TElement('span');
        $span->{'class'} = 'input-group-addon tcolor';
        
        $outer_size = 'undefined';
        if (strstr($this->size, '%') !== false) {
            $outer_size = $this->size;
            $this->size = '100%';
        }
        
        if ($this->changeAction) {
            if (!TForm::getFormByName($this->formName) instanceof TForm) {
                throw new Exception(AdiantiCoreTranslator::translate('You must pass the ^1 (^2) as a parameter to ^3', __CLASS__, $this->name, 'TForm::setFields()'));
            }
            
            $string_action = $this->changeAction->serialize(false);
            $this->setProperty('changeaction', "__adianti_post_lookup('{$this->formName}', '{$string_action}', '{$this->id}', 'callback')");
            $this->changeFunction = $this->getProperty('changeaction');
        }
        
        $i = new TElement('i');
        $i->{'class'} = 'tcolor-icon';
        $span->add($i);
        ob_start();
        parent::show();
        $child = ob_get_contents();
        ob_end_clean();
        $wrapper->add($child);
        $wrapper->add($span);
        $wrapper->show();
        
        TScript::create("tcolor_start('{$this->id}', '{$outer_size}', function(color) { {$this->changeFunction} }); ");
        
        if (!parent::getEditable()) {
            self::disableField($this->formName, $this->name);
        }
    }
}
