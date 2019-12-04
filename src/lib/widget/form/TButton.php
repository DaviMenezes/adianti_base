<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Base\TScript;
use Adianti\Base\Lib\Widget\Util\TImage;
use Exception;

/**
 * Button Widget
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TButton extends TField implements AdiantiWidgetInterface
{
    /**@var TAction*/
    private $action;
    private $image;
    private $properties;
    private $functions;
    private $tagName;
    protected $label;
    protected $formName;

    /**
     * Create a button with icon and action
     * @param string $name
     * @param array $callback
     * @param string $label
     * @param string $image
     * @return TButton
     * @throws \ReflectionException
     * @throws Exception
     */
    public static function create(string $name, array $callback, string $label, string $image)
    {
        $button = new TButton($name);
        $button->setAction(new TAction($callback), $label);
        $button->setImage($image);
        return $button;
    }
    
    /**
     * Add CSS class
     */
    public function addStyleClass($class)
    {
        $this->{'class'} = 'btn btn-default '. $class;
    }
    
    /**
     * Define the action of the button
     * @param  $action TAction object
     * @param  $label  Button's label
     */
    public function setAction(TAction $action, $label = null)
    {
        $this->action = $action;
        $this->label  = $label;
    }
    
    /**
     * Returns the buttona action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Define the tag name
     * @param string $name tag name
     */
    public function setTagName(string $name)
    {
        $this->tagName = $name;
    }

    /**
     * Define the icon of the button
     * @param string $image image path
     */
    public function setImage(string $image)
    {
        $this->image = $image;
    }

    /**
     * Define the label of the button
     * @param string $label button label
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
    }
    
    /**
     * Returns the button label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Add a JavaScript function to be executed by the button
     * @param string $function A piece of JavaScript code
     * @ignore-autocomplete on
     */
    public function addFunction(string $function)
    {
        if ($function) {
            $this->functions = $function.';';
        }
    }

    /**
     * Define a field property
     * @param string $name Property Name
     * @param string $value Property Value
     * @param bool $replace
     */
    public function setProperty(string $name, string $value, bool $replace = true)
    {
        $this->properties[$name] = $value;
    }

    /**
     * Return field property
     * @param string $name
     * @return string
     */
    public function getProperty(string $name)
    {
        return $this->properties[$name];
    }

    /**
     * Enable the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function enableField(string $form_name, string $field_name)
    {
        TScript::create(" tbutton_enable_field('{$form_name}', '{$field_name}'); ");
    }

    /**
     * Disable the field
     * @param string $form_name Form name
     * @param object $field Field name
     */
    public static function disableField(string $form_name, object $field)
    {
        TScript::create(" tbutton_disable_field('{$form_name}', '{$field}'); ");
    }
    
    /**
     * Show the widget at the screen
     */
    public function show()
    {
        if ($this->action) {
            if (empty($this->formName)) {
                $label = ($this->label instanceof TLabel) ? $this->label->getValue() : $this->label;
                throw new Exception(AdiantiCoreTranslator::translate('You must pass the ^1 (^2) as a parameter to ^3', __CLASS__, $label, 'TForm::setFields()'));
            }
            
            // get the action as URL
            $route = $this->action->serialize(false);
            if ($this->action->isStatic()) {
                $route .= '&static=1';
            }
            $wait_message = AdiantiCoreTranslator::translate('Loading');
            // define the button's action (ajax post)
            $action = "Adianti.waitMessage = '$wait_message';";
            $action.= "{$this->functions}";
            $action.= "__adianti_post_data('{$this->formName}', '{$route}');";
            $action.= "return false;";
                        
            $button = new TElement(!empty($this->tagName)? $this->tagName : 'button');
            $button->{'id'}      = 'tbutton_'.$this->name;
            $button->{'name'}    = $this->name;
            $button->{'class'}   = 'btn btn-default btn-sm';
            $button->{'onclick'} = $action;
            $action = '';
        } else {
            $action = $this->functions;
            // creates the button using a div
            $button = new TElement(!empty($this->tagName)? $this->tagName : 'div');
            $button->{'id'}      = 'tbutton_'.$this->name;
            $button->{'name'}    = $this->name;
            $button->{'class'}   = 'btn btn-default btn-sm';
            $button->{'onclick'} = $action;
        }
        
        if ($this->properties) {
            foreach ($this->properties as $property => $value) {
                $button->$property = $value;
            }
        }

        $span = new TElement('span');
        if ($this->image) {
            $image = new TImage($this->image);
            $image->{'style'} .= ';padding-right:4px';
            $span->add($image);
        }
        
        if ($this->label) {
            $span->add($this->label);
        }
        $button->add($span);
        $button->show();
    }
}
