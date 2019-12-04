<?php
namespace Adianti\Base\Lib\Widget\Datagrid;

use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Dvi\Component\Widget\Util\Action;
use Exception;

/**
 * Represents an action inside a datagrid
 *
 * @version    5.5
 * @package    widget
 * @subpackage datagrid
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TDataGridAction extends Action //Action is a DviLibrary custom class
{
    private $field;
    private $fields;
    private $image;
    private $label;
    private $buttonClass;
    private $useButton;
    private $displayCondition;
    
    /**
     * Define wich Active Record's property will be passed along with the action
     * @param object $field Active Record's property
     */
    public function setField($field)
    {
        $this->field = $field;
        
        $this->setParameter('key', '{'.$field.'}');
        $this->setParameter($field, '{'.$field.'}');
    }

    /**
     * Define wich Active Record's properties will be passed along with the action
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        
        if ($fields) {
            if (empty($this->field)) {
                $this->setParameter('key', '{'.$fields[0].'}');
            }
            
            foreach ($fields as $field) {
                $this->setParameter($field, '{'.$field.'}');
            }
        }
    }
    
    /**
     * Returns the Active Record's property that
     * will be passed along with the action
     */
    public function getField()
    {
        return $this->field;
    }
    
    /**
     * Returns the Active Record's properties that
     * will be passed along with the action
     */
    public function getFields()
    {
        return $this->fields;
    }
    
    /**
     * Return if there at least one field defined
     */
    public function fieldDefined()
    {
        return (!empty($this->field) or !empty($this->fields));
    }

    /**
     * Define an icon for the action
     * @param string $image The Image path
     */
    public function setImage(string $image)
    {
        $this->image = $image;
    }
    
    /**
     * Returns the icon of the action
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * define the label for the action
     * @param string $label A string containing a text label
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
    }
    
    /**
     * Returns the text label for the action
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * define the buttonClass for the action
     * @param string $buttonClass A string containing the button css class
     */
    public function setButtonClass(string $buttonClass)
    {
        $this->buttonClass = $buttonClass;
    }
    
    /**
     * Returns the buttonClass
     */
    public function getButtonClass()
    {
        return $this->buttonClass;
    }

    /**
     * define if the action will use a regular button
     * @param bool $useButton A boolean
     */
    public function setUseButton(bool $useButton)
    {
        $this->useButton = $useButton;
    }
    
    /**
     * Returns if the action will use a regular button
     */
    public function getUseButton()
    {
        return $this->useButton;
    }
    
    /**
     * Define a callback that must be valid to show the action
     * @param Callback $displayCondition Action display condition
     */
    public function setDisplayCondition(/*Callable*/ $displayCondition)
    {
        $this->displayCondition = $displayCondition;
    }
    
    /**
     * Returns the action display condition
     */
    public function getDisplayCondition()
    {
        return $this->displayCondition;
    }

    /**
     * Prepare action for use over an object
     * @param object $object Data Object
     * @return TAction
     * @throws Exception
     */
    public function prepare($object)
    {
        if (!$this->fieldDefined()) {
            throw new Exception(AdiantiCoreTranslator::translate('Field for action ^1 not defined', parent::toString()) . '.<br>' .
                                AdiantiCoreTranslator::translate('Use the ^1 method', 'setField'.'()').'.');
        }
        
        if ($field = $this->getField()) {
            if (!isset($object->$field)) {
                throw new Exception(AdiantiCoreTranslator::translate('Field ^1 not exists or contains NULL value', $field));
            }
        }
        
        if ($fields = $this->getFields()) {
            $field = $fields[0];
            
            if (!isset($object->$field)) {
                throw new Exception(AdiantiCoreTranslator::translate('Field ^1 not exists or contains NULL value', $field));
            }
        }
        
        return parent::prepare($object);
    }

    /**
     * Converts the action into an URL
     * @param bool $format_action = format action with document or javascript (ajax=no)
     * @return string
     */
    public function serialize($format_action = true)
    {
        return parent::serialize($format_action);
    }
}
