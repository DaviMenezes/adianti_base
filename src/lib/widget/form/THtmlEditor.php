<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Core\AdiantiApplicationConfig;
use Adianti\Base\Lib\Widget\Form\AdiantiWidgetInterface;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Base\TScript;
use Adianti\Base\Lib\Widget\Form\TField;

/**
 * Html Editor
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class THtmlEditor extends TField implements AdiantiWidgetInterface
{
    protected $id;
    protected $size;
    protected $formName;
    private $height;
    
    /**
     * Class Constructor
     * @param $name Widet's name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->id = 'THtmlEditor_'.mt_rand(1000000000, 1999999999);
        
        // creates a tag
        $this->tag = new TElement('textarea');
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
     * Returns the size
     * @return array(width, height)
     */
    public function getSize()
    {
        return array( $this->size, $this->height );
    }

    /**
     * Enable the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function enableField(string $form_name, string $field_name)
    {
        TScript::create(" thtmleditor_enable_field('{$form_name}', '{$field_name}'); ");
    }

    /**
     * Disable the field
     * @param string $form_name Form name
     * @param object $field Field name
     */
    public static function disableField(string $form_name, object $field)
    {
        TScript::create(" thtmleditor_disable_field('{$form_name}', '{$field}'); ");
    }

    /**
     * Clear the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function clearField(string $form_name, string $field_name)
    {
        TScript::create(" thtmleditor_clear_field('{$form_name}', '{$field_name}'); ");
    }
    
    /**
     * Show the widget
     */
    public function show()
    {
        $this->tag->{'id'} = $this->id;
        $this->tag->{'class'}  = 'thtmleditor';       // CSS
        $this->tag->{'widget'} = 'thtmleditor';
        $this->tag->{'name'}   = $this->name;   // tag name
        
        $ini = AdiantiApplicationConfig::get();
        $locale = !empty($ini['general']['locale']) ? $ini['general']['locale'] : 'pt-BR';
        
        // add the content to the textarea
        $this->tag->add(htmlspecialchars($this->value));
        
        // show the tag
        $this->tag->show();
        
        TScript::create(" thtmleditor_start( '{$this->tag->{'id'}}', '{$this->size}', '{$this->height}', '{$locale}' ); ");
        
        // check if the field is not editable
        if (!parent::getEditable()) {
            TScript::create(" thtmleditor_disable_field('{$this->formName}', '{$this->name}'); ");
        }
    }
}
