<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Base\TScript;
use Exception;

/**
 * Multi Entry Widget
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Matheus Agnes Dias
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TMultiEntry extends TSelect implements AdiantiWidgetInterface
{
    protected $id;
    protected $items;
    protected $size;
    protected $height;
    protected $maxSize;
    protected $editable;
    protected $changeAction;
    protected $changeFunction;
    
    /**
     * Class Constructor
     * @param  $name Widget's name
     */
    public function __construct($name)
    {
        // executes the parent class constructor
        parent::__construct($name);
        $this->id   = 'tmultientry_'.mt_rand(1000000000, 1999999999);

        $this->height = 100;
        $this->maxSize = 0;
        
        $this->tag->{'component'} = 'multientry';
        $this->tag->{'widget'} = 'tmultientry';
    }
    
    /**
     * Define the widget's size
     * @param  $width   Widget's width
     * @param  $height  Widget's height
     */
    public function setSize($width, $height = null)
    {
        $this->size   = $width;
        if ($height) {
            $this->height = $height;
        }
    }

    /**
     * Define the maximum number of items that can be selected
     */
    public function setMaxSize($maxsize)
    {
        $this->maxSize = $maxsize;
    }

    /**
     * Enable the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function enableField(string $form_name, string $field_name)
    {
        TScript::create(" tmultisearch_enable_field('{$form_name}', '{$field_name}'); ");
    }

    /**
     * Disable the field
     * @param string $form_name Form name
     * @param object $field Field name
     */
    public static function disableField(string $form_name, object $field)
    {
        TScript::create(" tmultisearch_disable_field('{$form_name}', '{$field}'); ");
    }

    /**
     * Clear the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function clearField(string $form_name, string $field_name)
    {
        TScript::create(" tmultisearch_clear_field('{$form_name}', '{$field_name}'); ");
    }
    
    /**
     * Render items
     */
    protected function renderItems($with_titles = true)
    {
        if (parent::getValue()) {
            // iterate the combobox items
            foreach (parent::getValue() as $item) {
                // creates an <option> tag
                $option = new TElement('option');
                $option->{'value'} = $item;  // define the index
                $option->add($item);      // add the item label
                
                if ($with_titles) {
                    $option->{'title'} = $item;  // define the title
                }
                
                // mark as selected
                $option->{'selected'} = 1;
                
                $this->tag->add($option);
            }
        }
    }
    
    /**
     * Shows the widget
     */
    public function show()
    {
        // define the tag properties
        $this->tag->{'name'}  = $this->name.'[]';    // tag name
        $this->tag->{'id'}  = $this->id;    // tag name
        
        if (strstr($this->size, '%') !== false) {
            $this->setProperty('style', "width:{$this->size};", false); //aggregate style info
            $size  = "{$this->size}";
        } else {
            $this->setProperty('style', "width:{$this->size}px;", false); //aggregate style info
            $size  = "{$this->size}px";
        }
        
        $change_action = 'function() {}';
        
        $this->renderItems(false);
        
        if ($this->editable) {
            if (isset($this->changeAction)) {
                if (!TForm::getFormByName($this->formName) instanceof TForm) {
                    throw new Exception(AdiantiCoreTranslator::translate('You must pass the ^1 (^2) as a parameter to ^3', __CLASS__, $this->name, 'TForm::setFields()'));
                }
                
                $string_action = $this->changeAction->serialize(false);
                $change_action = "function() { __adianti_post_lookup('{$this->formName}', '{$string_action}', '{$this->id}', 'callback'); }";
            } elseif (isset($this->changeFunction)) {
                $change_action = "function() { $this->changeFunction }";
            }
            $this->tag->show();
            TScript::create(" tmultientry_start( '{$this->id}', '{$this->maxSize}', '{$size}', '{$this->height}px', $change_action ); ");
        } else {
            $this->tag->show();
            TScript::create(" tmultientry_start( '{$this->id}', '{$this->maxSize}', '{$size}', '{$this->height}px', $change_action ); ");
            TScript::create(" tmultientry_disable_field( '{$this->formName}', '{$this->name}'); ");
        }
    }
}
