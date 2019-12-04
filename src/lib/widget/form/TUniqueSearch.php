<?php
namespace Adianti\Base\Lib\Widget\Form;

use Exception;

/**
 * Unique Search Widget
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TUniqueSearch extends TMultiSearch implements AdiantiWidgetInterface
{
    /**
     * Class Constructor
     * @param  $name Widget's name
     */
    public function __construct($name)
    {
        // executes the parent class constructor
        parent::__construct($name);
        parent::setMaxSize(1);
        parent::setDefaultOption(TRUE);
        parent::disableMultiple();
        
        $this->tag->{'widget'} = 'tuniquesearch';
    }

    public function setValue(?string $value)
    {
        $this->value = $value; // avoid use parent::setValue() because compat mode
    }
    
    /**
     * Return the post data
     */
    public function getPostData()
    {
        if (isset($_POST[$this->name]))
        {
            $val = $_POST[$this->name];
            return $val;
        }
        else
        {
            return '';
        }
    }

    /**
     * Show the component
     * @throws Exception
     */
    public function show()
    {
        $this->tag->{'name'}  = $this->name; // tag name
        parent::show();
    }
}
