<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Widget\Base\TScript;
use Exception;

/**
 * Spinner Widget (also known as spin button)
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TSpinner extends TField implements AdiantiWidgetInterface
{
    private $min;
    private $max;
    private $step;
    private $exitAction;
    private $exitFunction;
    protected $id;
    protected $formName;
    protected $value;

    /**
     * Class Constructor
     * @param string $name Name of the widget
     * @throws \ReflectionException
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->id = 'tspinner_'.mt_rand(1000000000, 1999999999);
    }

    /**
     * Define the field's range
     * @param string $min Minimal value
     * @param string $max Maximal value
     * @param string $step Step value
     * @throws Exception
     */
    public function setRange(string $min, string $max, string $step)
    {
        $this->min = $min;
        $this->max = $max;
        $this->step = $step;
        
        if ($step == 0) {
            throw new Exception(AdiantiCoreTranslator::translate('Invalid parameter (^1) in ^2', $step, 'setRange'));
        }
        
        if (is_int($step) and $this->getValue() % $step !== 0) {
            parent::setValue($min);
        }
    }

    /**
     * Define the action to be executed when the user leaves the form field
     * @param $action TAction object
     * @throws Exception
     */
    public function setExitAction(TAction $action)
    {
        if ($action->isStatic()) {
            $this->exitAction = $action;
        } else {
            $string_action = $action->toString();
            throw new Exception(AdiantiCoreTranslator::translate(
                'Action (^1) must be static to be used in ^2',
                $string_action,
                __METHOD__
            ));
        }
    }

    /**
     * Enable the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function enableField(string $form_name, string $field_name)
    {
        TScript::create(" tspinner_enable_field('{$form_name}', '{$field_name}'); ");
    }

    /**
     * Disable the field
     * @param string $form_name Form name
     * @param object $field Field name
     */
    public static function disableField(string $form_name, object $field)
    {
        TScript::create(" tspinner_disable_field('{$form_name}', '{$field}'); ");
    }
    
    /**
     * Set exit function
     */
    public function setExitFunction($function)
    {
        $this->exitFunction = $function;
    }
    
    /**
     * Shows the widget at the screen
     */
    public function show()
    {
        // define the tag properties
        $this->tag->{'name'}  = $this->name;    // TAG name
        $this->tag->{'value'} = $this->value;   // TAG value
        $this->tag->{'type'}  = 'text';         // input type
        
        if (strstr($this->size, '%') !== false) {
            $this->setProperty('style', "width:{$this->size};", false); //aggregate style info
            $this->setProperty('relwidth', "{$this->size}", false); //aggregate style info
        } else {
            $this->setProperty('style', "width:{$this->size}px;", false); //aggregate style info
        }
        
        if ($this->id) {
            $this->tag->{'id'}  = $this->id;
        }
        
        $exit_action = 'function() {}';
        if (isset($this->exitAction)) {
            if (!TForm::getFormByName($this->formName) instanceof TForm) {
                throw new Exception(AdiantiCoreTranslator::translate(
                    'You must pass the ^1 (^2) as a parameter to ^3',
                    __CLASS__,
                    $this->name,
                    'TForm::setFields()'
                ));
            }
            $string_action = $this->exitAction->serialize(false);
            $exit_action = "function() { __adianti_post_lookup('{$this->formName}', '{$string_action}', '{$this->id}' ) }";
        }
        
        if (isset($this->exitFunction)) {
            $exit_action = "function() { {$this->exitFunction} }";
        }
        
        $mask = str_repeat('9', strlen($this->max));
        $this->tag->{'onKeyPress'} = "return tentry_mask(this,event,'{$mask}')";
        $this->tag->show();
        TScript::create(" tspinner_start( '#{$this->id}', '{$this->value}', '{$this->min}', '{$this->max}', '{$this->step}', $exit_action); ");
        
        // verify if the widget is non-editable
        if (!parent::getEditable()) {
            self::disableField($this->formName, $this->name);
        }
    }

    public function setValue(?string $value)
    {
        parent::setValue((float) $value);
    }
}
