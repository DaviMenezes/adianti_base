<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Base\TScript;

/**
 * Color Widget
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TIcon extends TEntry implements AdiantiWidgetInterface
{
    protected $id;
    protected $changeFunction;
    protected $formName;
    protected $name;

    /**
     * Class Constructor
     * @param string $name Name of the widget
     * @throws \ReflectionException
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->id = 'ticon_'.mt_rand(1000000000, 1999999999);
    }

    /**
     * Enable the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function enableField(string $form_name, string $field_name)
    {
        TScript::create(" ticon_enable_field('{$form_name}', '{$field_name}'); ");
    }

    /**
     * Disable the field
     * @param string $form_name Form name
     * @param object $field Field name
     */
    public static function disableField(string $form_name, object $field)
    {
        TScript::create(" ticon_disable_field('{$form_name}', '{$field}'); ");
    }
    
    /**
     * Set change function
     */
    public function setChangeFunction($function)
    {
        $this->changeFunction = $function;
    }

    /**
     * Shows the widget at the screen
     * @throws \Exception
     */
    public function show()
    {
        $wrapper = new TElement('div');
        $wrapper->{'class'} = 'input-group';
        $span = new TElement('span');
        $span->{'class'} = 'input-group-addon';
        
        if (!empty($this->exitAction)) {
            $this->setChangeFunction($this->changeFunction . "; tform_fire_field_actions('{$this->formName}', '{$this->name}'); ");
        }
        
        $i = new TElement('i');
        $span->add($i);
        
        if (strstr($this->size, '%') !== false) {
            $outer_size = $this->size;
            $this->size = '100%';
            $wrapper->{'style'} = "width: $outer_size";
        }
        
        ob_start();
        parent::show();
        $child = ob_get_contents();
        ob_end_clean();
        
        $wrapper->add($child);
        $wrapper->add($span);
        $wrapper->show();
        
        if (parent::getEditable()) {
            if ($this->changeFunction) {
                TScript::create(" ticon_start('{$this->id}',function(icon){ {$this->changeFunction} }); ");
            } else {
                TScript::create(" ticon_start('{$this->id}',false); ");
            }
        }
    }
}
