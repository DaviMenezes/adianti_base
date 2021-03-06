<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Base\TScript;
use Exception;

/**
 * Select Widget
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TSelect extends TField implements AdiantiWidgetInterface
{
    protected $id;
    protected $height;
    protected $items; // array containing the combobox options
    protected $formName;
    protected $changeFunction;
    protected $changeAction;
    protected $defaultOption;
    protected $separator;
    protected $value;
    
    public function __construct(string $name)
    {
        // executes the parent class constructor
        parent::__construct($name);
        $this->id   = 'tselect_' . mt_rand(1000000000, 1999999999);
        $this->defaultOption = '';

        // creates a <select> tag
        $this->tag = new TElement('select');
        $this->tag->{'class'} = 'tcombo'; // CSS
        $this->tag->{'multiple'} = '1';
        $this->tag->{'widget'} = 'tselect';
    }
    
    
    /**
     * Disable multiple selection
     */
    public function disableMultiple()
    {
        unset($this->tag->{'multiple'});
        $this->tag->{'size'} = 3;
    }

    public function setDefaultOption($option)
    {
        $this->defaultOption = $option;
    }

    /**
     * Add items to the select
     * @param array $items An indexed array containing the combo options
     */
    public function addItems(array $items)
    {
        if (is_array($items)) {
            $this->items = $items;
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
     * Define the Field's width
     * @param $width Field's width in pixels
     * @param $height Field's height in pixels
     */
    public function setSize($width, $height = null)
    {
        $this->size = $width;
        $this->height = $height;
    }
    
    /**
     * Returns the size
     * @return array(width, height)
     */
    public function getSize()
    {
        return array( $this->size, $this->height );
    }

    /**
     * Define the field's separator
     * @param string $sep A string containing the field's separator
     */
    public function setValueSeparator(string $sep)
    {
        $this->separator = $sep;
    }

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
            if ($this->tag->{'multiple'}) {
                if (empty($this->separator)) {
                    return $_POST[$this->name];
                } else {
                    return implode($this->separator, $_POST[$this->name]);
                }
            } else {
                return $_POST[$this->name][0];
            }
        } else {
            return array();
        }
    }

    /**
     * Define the action to be executed when the user changes the combo
     * @param $action TAction object
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
     * Reload combobox items after it is already shown
     * @param string $formname form name (used in gtk version)
     * @param string $name field name
     * @param $items array with items
     * @param bool $startEmpty ...
     */
    public static function reload(string $formname, string $name, array $items, bool $startEmpty = false)
    {
        $code = "tselect_clear('{$formname}', '{$name}'); ";
        if ($startEmpty) {
            $code .= "tselect_add_option('{$formname}', '{$name}', '', ''); ";
        }
        
        if ($items) {
            foreach ($items as $key => $value) {
                $code .= "tselect_add_option('{$formname}', '{$name}', '{$key}', '{$value}'); ";
            }
        }
        TScript::create($code);
    }

    /**
     * Enable the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function enableField(string $form_name, string $field_name)
    {
        TScript::create(" tselect_enable_field('{$form_name}', '{$field_name}'); ");
    }

    /**
     * Disable the field
     * @param string $form_name Form name
     * @param object $field Field name
     */
    public static function disableField(string $form_name, object $field)
    {
        TScript::create(" tselect_disable_field('{$form_name}', '{$field}'); ");
    }

    /**
     * Clear the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function clearField(string $form_name, string $field_name)
    {
        TScript::create(" tselect_clear_field('{$form_name}', '{$field_name}'); ");
    }

    /**
     * Render items
     * @param bool $with_titles
     */
    protected function renderItems(bool $with_titles = true)
    {
        if ($this->defaultOption !== false) {
            // creates an empty <option> tag
            $option = new TElement('option');
            
            $option->add($this->defaultOption);
            $option->{'value'} = '';   // tag value

            // add the option tag to the combo
            $this->tag->add($option);
        }
        
        if ($this->items) {
            // iterate the combobox items
            foreach ($this->items as $chave => $item) {
                if (substr($chave, 0, 3) == '>>>') {
                    $optgroup = new TElement('optgroup');
                    $optgroup->{'label'} = $item;
                    // add the option to the combo
                    $this->tag->add($optgroup);
                } else {
                    // creates an <option> tag
                    $option = new TElement('option');
                    $option->{'value'} = $chave;  // define the index
                    if ($with_titles) {
                        $option->{'title'} = $item;  // define the title
                    }
                    $option->{'titside'} = 'right';  // define the title side
                    $option->add(htmlspecialchars($item));      // add the item label
                    
                    // verify if this option is selected
                    if ((is_array($this->value)  and @in_array($chave, $this->value))
                        or
                         (
                             is_scalar($this->value)
                             and strlen((string) $this->value) > 0
                             and @in_array($chave, (array) $this->value)
                         )
                    ) {
                        // mark as selected
                        $option->{'selected'} = 1;
                    }
                    
                    if (isset($optgroup)) {
                        $optgroup->add($option);
                    } else {
                        $this->tag->add($option);
                    }
                }
            }
        }
    }

    /**
     * Shows the widget
     * @throws Exception
     */
    public function show()
    {
        // define the tag properties
        $this->tag->{'name'}  = $this->name.'[]';    // tag name
        $this->tag->{'id'}    = $this->id;

        $this->setStyle();

        // verify whether the widget is editable
        if (parent::getEditable()) {
            if (isset($this->changeAction)) {
                if (!TForm::getFormByName($this->formName) instanceof TForm) {
                    throw new Exception(AdiantiCoreTranslator::translate(
                        'You must pass the ^1 (^2) as a parameter to ^3',
                        __CLASS__,
                        $this->name,
                        'TForm::setFields()'
                    ));
                }
                
                $string_action = $this->changeAction->serialize(false);
                $this->setProperty(
                    'changeaction',
                    "__adianti_post_lookup('{$this->formName}', '{$string_action}', this, 'callback')"
                );
                $this->setProperty('onChange', $this->getProperty('changeaction'));
            }
            
            if (isset($this->changeFunction)) {
                $this->setProperty('changeaction', $this->changeFunction, false);
                $this->setProperty('onChange', $this->changeFunction, false);
            }
        } else {
            // make the widget read-only
            $this->tag->{'onclick'} = "return false;";
            $this->tag->{'style'}  .= ';pointer-events:none';
            $this->tag->{'class'}   = 'tfield_disabled'; // CSS
        }
        
        // shows the widget
        $this->renderItems();
        $this->tag->show();
    }

    protected function setStyle()
    {
        $this->setProperty('style', (strstr($this->size, '%') !== false) ? "width:{$this->size}" : "width:{$this->size}px", false); //aggregate style info
        $this->setProperty('style', (strstr($this->height, '%') !== false) ? "height:{$this->height}" : "height:{$this->height}px", false); //aggregate style info
    }
}
