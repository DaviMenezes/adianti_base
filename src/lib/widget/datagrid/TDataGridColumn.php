<?php
namespace Adianti\Base\Lib\Widget\Datagrid;

use Adianti\Base\Lib\Control\TAction;

/**
 * Representes a DataGrid column
 *
 * @version    5.5
 * @package    widget
 * @subpackage datagrid
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TDataGridColumn
{
    private $name;
    private $label;
    private $align;
    private $width;
    protected $action;
    private $editaction;
    private $transformer;
    private $properties;
    private $dataProperties;
    private $totalFunction;
    
    /**
     * Class Constructor
     * @param  $name  = Name of the column in the database
     * @param  $label = Text label that will be shown in the header
     * @param  $align = Column align (left, center, right)
     * @param  $width = Column Width (pixels)
     */
    public function __construct(string $name, string $label, string $align, string $width = null)
    {
        $this->name  = $name;
        $this->label = $label;
        $this->align = $align;
        $this->width = $width;
        $this->properties = array();
        $this->dataProperties = array();
    }

    /**
     * Define a column header property
     * @param string $name Property Name
     * @param string $value Property Value
     */
    public function setProperty(string $name, string $value)
    {
        $this->properties[$name] = $value;
    }

    /**
     * Define a data property
     * @param string $name Property Name
     * @param string $value Property Value
     */
    public function setDataProperty(string $name, string $value)
    {
        $this->dataProperties[$name] = $value;
    }

    /**
     * Return a column property
     * @param string $name Property Name
     * @return mixed
     */
    public function getProperty(string $name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }
    }

    /**
     * Return a data property
     * @param string $name Property Name
     * @return mixed
     */
    public function getDataProperty(string $name)
    {
        if (isset($this->dataProperties[$name])) {
            return $this->dataProperties[$name];
        }
    }
    
    /**
     * Return column properties
     */
    public function getProperties()
    {
        return $this->properties;
    }
    
    /**
     * Return data properties
     */
    public function getDataProperties()
    {
        return $this->dataProperties;
    }

    /**
     * Intercepts whenever someones assign a new property's value
     * @param string $name Property Name
     * @param mixed $value    Property Value
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
     * Returns the database column's name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Returns the column's label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the column's label
     * @param string $label column label
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
    }
    
    /**
     * Returns the column's align
     */
    public function getAlign()
    {
        return $this->align;
    }
    
    /**
     * Returns the column's width
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Define the action to be executed when
     * the user clicks over the column header
     * @param TAction $action object
     * @param array $parameters Action parameters
     */
    public function setAction(TAction $action, array $parameters = null)
    {
        $this->action = $action;
        
        if ($parameters) {
            $this->action->setParameters($parameters);
        }
    }
    
    /**
     * Is Returns the action defined by set_action() method
     * user clicks over the column header
     * @return TAction the action to be executed when the
     */
    public function getAction()
    {
        // verify if the column has an actions
        if ($this->action) {
            return $this->action;
        }
    }

    /**
     * Define the action to be executed when
     * the user clicks do edit the column
     * @param TDataGridAction $editaction
     */
    public function setEditAction(TDataGridAction $editaction)
    {
        $this->editaction = $editaction;
    }
    
    /**
     * Returns the action defined by setEditAction() method
     * user clicks do edit the column
     * @return TAction the action to be executed when the
     */
    public function getEditAction()
    {
        // verify if the column has an actions
        if ($this->editaction) {
            return $this->editaction;
        }
    }

    /**
     * Define a callback function to be applyed over the column's data
     * @param callable $callback A function name of a method of an object
     */
    public function setTransformer(callable $callback)
    {
        $this->transformer = $callback;
    }

    /**
     * Returns the callback defined by the setTransformer()
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * Define a callback function to totalize column
     * @param callable $callback A function name of a method of an object
     */
    public function setTotalFunction(callable $callback)
    {
        $this->totalFunction = $callback;
    }
    
    /**
     * Returns the callback defined by the setTotalFunction()
     */
    public function getTotalFunction()
    {
        return $this->totalFunction;
    }
}
