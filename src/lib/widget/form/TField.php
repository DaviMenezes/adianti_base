<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Base\TScript;
use Adianti\Base\Lib\Validator\TFieldValidator;
use Adianti\Base\Lib\Validator\TRequiredValidator;

use Dvi\Component\Widget\Form\Field\Contract\ValidatorContract;
use Exception;
use ReflectionClass;

/**
 * Base class to construct all the widgets
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
abstract class TField
{
    protected $id;
    protected $name;
    protected $size;
    protected $value;
    protected $editable;
    protected $tag;
    protected $formName;
    protected $label;
    private $validations;

    /**
     * Class Constructor
     * @param string $name name of the field
     * @throws \ReflectionException
     */
    public function __construct(string $name)
    {
        $rc = new ReflectionClass($this);
        $classname = $rc->getShortName();
        
        if (empty($name)) {
            throw new Exception(AdiantiCoreTranslator::translate('The parameter (^1) of ^2 constructor is required', 'name', $classname));
        }
        
        // define some default properties
        self::setEditable(true);
        self::setName(trim($name));
        
        // initialize validations array
        $this->validations = array();
        
        // creates a <input> tag
        $this->tag = new TElement('input');
        $this->tag->{'class'} = 'tfield';   // classe CSS
        $this->tag->{'widget'} = strtolower($classname);
    }

    /**
     * Intercepts whenever someones assign a new property's value
     * @param string $name Property Name
     * @param int|float|string $value Property Value
     */
    public function __set(string $name, $value)
    {
        // objects and arrays are not set as properties
        if (is_scalar($value)) {
            // store the property's value
            $this->setProperty($name, $value);
        }
    }

    /**
     * Returns a property value
     * @param string $name Property Name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->getProperty($name);
    }
    
    /**
     * Clone the object
     */
    public function __clone()
    {
        $this->tag = clone $this->tag;
    }

    /**
     * Redirects function call
     * @param string $method Method name
     * @param array $param Array of parameters
     * @return mixed
     * @throws Exception
     */
    public function __call(string $method, array $param)
    {
        if (method_exists($this->tag, $method)) {
            return call_user_func_array(array($this->tag, $method), $param);
        } else {
            throw new Exception(AdiantiCoreTranslator::translate("Method ^1 not found", $method.'()'));
        }
    }

    /**
     * Define the field's label
     * @param string $label A string containing the field's label
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    /**
     * Returns the field's label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Define the field's name
     * @param string $name A string containing the field's name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns the field's name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Define the field's id
     * @param string $id A string containing the field's id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * Returns the field's id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Define the field's value
     * @param string $value A string containing the field's value
     */
    public function setValue(?string $value)
    {
        $this->value = $value;
    }
    
    /**
     * Returns the field's value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Define the name of the form to wich the field is attached
     * @param string $name A string containing the name of the form
     * @ignore-autocomplete on
     */
    public function setFormName(string $name)
    {
        $this->formName = $name;
    }
    
    /**
     * Return the name of the form to wich the field is attached
     */
    public function getFormName()
    {
        return $this->formName;
    }

    /**
     * Define the field's tooltip
     * @param string $tip
     */
    public function setTip(?string $tip)
    {
        if (!$tip) {
            return;
        }
        $this->tag->{'title'} = $tip;
    }
    
    /**
     * Return the post data
     */
    public function getPostData()
    {
        if (isset($_POST[$this->name])) {
            return $_POST[$this->name];
        } else {
            return '';
        }
    }

    /**
     * Define if the field is editable
     * @param bool $editable A boolean
     */
    public function setEditable(bool $editable)
    {
        $this->editable= $editable;
    }

    /**
     * Returns if the field is editable
     * @return bool A boolean
     */
    public function getEditable()
    {
        return $this->editable;
    }

    /**
     * Define a field property
     * @param string $name
     * @param string|null $value
     * @param bool $replace
     */
    public function setProperty(string $name, ?string $value, bool $replace = true)
    {
        if ($replace) {
            // delegates the property assign to the composed object
            $this->tag->$name = $value;
        } else {
            if ($this->tag->$name) {
                // delegates the property assign to the composed object
                $this->tag->$name = $this->tag->$name . ';' . $value;
            } else {
                // delegates the property assign to the composed object
                $this->tag->$name = $value;
            }
        }
    }

    /**
     * Return a field property
     * @param string $name Property Name
     * @return mixed
     */
    public function getProperty(string $name)
    {
        return $this->tag->$name;
    }
    
    /**
     * Define the Field's width
     * @param $width Field's width in pixels
     */
    public function setSize(string $width)
    {
        //Todo Dvi-custom
        $sufix = strstr($width, '%') === false ? 'px' : '';
        $this->size = "{$width}$sufix";
    }
    
    /**
     * Returns the field size
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Add a field validator
     * @param string $label Field name
     * @param $validator TFieldValidator object
     * @param array $parameters Aditional parameters
     */
    public function addValidation(string $label, ValidatorContract $validator, array $parameters = null)
    {
        $this->validations[] = array($label, $validator, $parameters);
    }
    
    /**
     * Returns field validations
     */
    public function getValidations()
    {
        return $this->validations;
    }
    
    /**
     * Returns if the field is required
     */
    public function isRequired()
    {
        if ($this->validations) {
            foreach ($this->validations as $validation) {
                $validator = $validation[1];
                if ($validator instanceof TRequiredValidator) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Validate a field
     */
    public function validate()
    {
        if ($this->validations) {
            foreach ($this->validations as $validation) {
                $label      = $validation[0];
                $validator  = $validation[1];
                $parameters = $validation[2];
                
                $validator->validate($label, $this->getValue(), $parameters);
            }
        }
    }
    
    /**
     * Returns the element content as a string
     */
    public function getContents()
    {
        ob_start();
        $this->show();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * Enable the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function enableField(string $form_name, string $field_name)
    {
        TScript::create(" tfield_enable_field('{$form_name}', '{$field_name}'); ");
    }

    /**
     * Disable the field
     * @param string $form_name Form name
     * @param object $field Field name
     */
    public static function disableField(string $form_name, object $field)
    {
        TScript::create(" tfield_disable_field('{$form_name}', '{$field}'); ");
    }

    /**
     * Clear the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function clearField(string $form_name, string $field_name)
    {
        TScript::create(" tfield_clear_field('{$form_name}', '{$field_name}'); ");
    }
}
