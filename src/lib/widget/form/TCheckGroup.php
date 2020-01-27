<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Base\TScript;
use Exception;

/**
 * A group of CheckButton's
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TCheckGroup extends TField implements AdiantiWidgetInterface
{
    protected $layout = 'vertical';
    /**@var TAction*/
    protected $changeAction;
    protected $items;
    protected $breakItems;
    protected $buttons;
    protected $labels;
    protected $allItemsChecked;
    protected $separator;
    protected $changeFunction;
    protected $formName;
    protected $labelClass;
    protected $useButton;
    protected $value;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->labelClass = 'tcheckgroup_label ';
        $this->useButton  = false;
    }
    
    /**
     * Clone object
     */
    public function __clone()
    {
        if (is_array($this->items)) {
            $oldbuttons = $this->buttons;
            $this->buttons = array();
            $this->labels  = array();

            foreach ($this->items as $key => $value) {
                $button = new TCheckButton("{$this->name}[]");
                $button->setProperty('checkgroup', $this->name);
                $button->setIndexValue($key);
                $button->setProperty('onchange', $oldbuttons[$key]->getProperty('onchange'));
                
                $obj = new TLabel($value);
                $this->buttons[$key] = $button;
                $this->labels[$key] = $obj;
            }
        }
    }
    
    /**
     * Check all options
     */
    public function checkAll()
    {
        $this->allItemsChecked = true;
    }

    /**
     * Define the direction of the options
     * @param string $direction String (vertical, horizontal)
     */
    public function setLayout(string $direction)
    {
        $this->layout = $direction;
    }
    
    /**
     * Get the direction (vertical or horizontal)
     */
    public function getLayout()
    {
        return $this->layout;
    }
    
    /**
     * Define after how much items, it will break
     */
    public function setBreakItems($breakItems)
    {
        $this->breakItems = $breakItems;
    }
    
    /**
     * Show as button
     */
    public function setUseButton()
    {
        $this->labelClass = 'btn btn-default ';
        $this->useButton  = true;
    }

    /**
     * Add items to the check group
     * @param array $items An indexed array containing the options
     * @throws \ReflectionException
     */
    public function addItems(array $items)
    {
        if (is_array($items)) {
            $this->items = $items;
            $this->buttons = array();
            $this->labels  = array();

            foreach ($items as $key => $value) {
                $button = new TCheckButton("{$this->name}[]");
                $button->setProperty('checkgroup', $this->name);
                $button->setIndexValue($key);

                $obj = new TLabel($this->getValueLabel($value));
                $this->buttons[$key] = $button;
                $this->labels[$key] = $obj;
            }
        }
    }
    
    /**
     * Return the items
     */
    public function getItems()
    {
        return $this->items;
    }
    
    /**
     * Return the option buttons
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * Return the option labels
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Define the field's separator
     * @param string $separator A string containing the field's separator
     */
    public function setValueSeparator(string $separator)
    {
        $this->separator = $separator;
    }

    /**
     * Define the field's value
     * @param string|null $value A string containing the field's value
     */
    public function setValue(?string $value)
    {
        if (empty($this->separator)) {
            $this->value = $value;
        } else {
            if ($value) {
                $this->value = explode($this->separator, $value);
            }
        }
    }
    
    /**
     * Return the post data
     */
    public function getPostData()
    {
        if (isset($_POST[$this->name])) {
            if (empty($this->separator)) {
                return $_POST[$this->name];
            } else {
                return implode($this->separator, $_POST[$this->name]);
            }
        } else {
            return array();
        }
    }

    /**
     * Define the action to be executed when the user changes the combo
     * @param TAction $action object
     * @throws Exception
     */
    public function setChangeAction(TAction $action)
    {
        if ($action->isStatic()) {
            $this->changeAction = $action;
        } else {
            $string_action = $action->toString();
            throw new Exception(AdiantiCoreTranslator::translate('Action (^1) must be static to be used in ^2', $string_action, __METHOD__));
        }
    }
    
    /**
     * Set change function
     */
    public function setChangeFunction($function)
    {
        $this->changeFunction = $function;
    }

    /**
     * Enable the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function enableField(string $form_name, string $field_name)
    {
        TScript::create(" tcheckgroup_enable_field('{$form_name}', '{$field_name}'); ");
    }

    /**
     * Disable the field
     * @param string $form_name Form name
     * @param object $field Field name
     */
    public static function disableField(string $form_name, object $field)
    {
        TScript::create(" tcheckgroup_disable_field('{$form_name}', '{$field}'); ");
    }

    /**
     * clear the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function clearField(string $form_name, string $field_name)
    {
        TScript::create(" tcheckgroup_clear_field('{$form_name}', '{$field_name}'); ");
    }
    
    /**
     * Shows the widget at the screen
     */
    public function show()
    {
        if ($this->useButton) {
            echo '<div data-toggle="buttons">';
            echo '<div class="btn-group" style="clear:both;float:left">';
        }
        
        if ($this->items) {
            // iterate the checkgroup options
            $i = 0;
            foreach ($this->items as $index => $label) {
                $button = $this->buttons[$index];
                $button->setName($this->name.'[]');
                $active = false;
                
                // verify if the checkbutton is checked
                if ((@in_array($index, $this->value) and !(is_null($this->value))) or $this->allItemsChecked) {
                    $button->setValue($index); // value=indexvalue (checked)
                    $active = true;
                }
                
                // create the label for the button
                $obj = $this->labels[$index];
                $obj->{'class'} = $this->labelClass . ($active?'active':'');
                $obj->setTip($this->tag->title);
                
                if ($this->getSize() and !$obj->getSize()) {
                    $obj->setSize($this->getSize());
                }
                
                // check whether the widget is non-editable
                if (parent::getEditable()) {
                    if (isset($this->changeAction)) {
                        if (!TForm::getFormByName($this->formName) instanceof TForm) {
                            throw new Exception(AdiantiCoreTranslator::translate('You must pass the ^1 (^2) as a parameter to ^3', __CLASS__, $this->name, 'TForm::setFields()'));
                        }
                        $string_action = $this->changeAction->serialize(false);
                        
                        $button->setProperty('changeaction', "__adianti_post_lookup('{$this->formName}', '{$string_action}', this, 'callback')");
                        $button->setProperty('onChange', $button->getProperty('changeaction'), false);
                    }
                    
                    if (isset($this->changeFunction)) {
                        $button->setProperty('changeaction', $this->changeFunction, false);
                        $button->setProperty('onChange', $this->changeFunction, false);
                    }
                } else {
                    $button->setEditable(false);
                    $obj->setFontColor('gray');
                }
                
                $obj->add($button);
                $obj->show();
                $i ++;
                
                if ($this->layout == 'vertical' or ($this->breakItems == $i)) {
                    $i = 0;
                    if ($this->useButton) {
                        echo '</div>';
                        echo '<div class="btn-group" style="clear:both;float:left">';
                    } else {
                        // shows a line break
                        $br = new TElement('br');
                        $br->show();
                    }
                }
                echo "\n";
            }
        }
        
        if ($this->useButton) {
            echo '</div>';
            echo '</div>';
        }
    }

    private function getValueLabel($value)
    {
        return is_object($value) ? $value->label : $value;
    }
}
